<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\MobilityStatus;
use App\Enums\MobilityType;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DisciplinaryIncident;
use App\Models\EducationalCenter;
use App\Models\ErasmusProject;
use App\Models\Mobility;
use App\Models\MobilityBond;
use App\Models\MobilityCall;
use App\Services\ApplicationScoringService;
use App\Services\MobilityOperationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MobilityOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dynamic_documents_include_minor_and_socio_sanitary_requirements(): void
    {
        $application = $this->application(['is_minor' => true, 'training_branch' => 'SOCIO_SANITARIA']);

        $this->assertContains(ApplicationDocument::MINOR_AUTHORIZATION, $application->requiredDocumentTypes());
        $this->assertContains(ApplicationDocument::CRIMINAL_BACKGROUND_CERTIFICATE, $application->requiredDocumentTypes());
        $this->assertContains(ApplicationDocument::SEXUAL_OFFENCES_CERTIFICATE, $application->requiredDocumentTypes());
        $this->assertDatabaseHas('application_documents', ['application_id' => $application->id, 'document_type' => ApplicationDocument::DEPOSIT_RECEIPT]);
    }

    public function test_iban_and_bond_are_validated_and_enable_pocket_money_alert_exactly_five_days_before_departure(): void
    {
        $application = $this->application();
        $application->documents()->update(['status' => ApplicationDocument::STATUS_VALIDATED]);
        $mobility = $this->mobility($application, ['start_date' => now()->addDays(5), 'iban' => 'ES9121000418450200051332', 'bank_account_holder' => $application->full_name]);
        MobilityBond::create(['mobility_id' => $mobility->id, 'amount' => 275, 'concept' => 'Fianza '.$mobility->participant_name, 'destination_iban' => MobilityBond::DESTINATION_IBAN, 'status' => 'validated']);

        $created = (new MobilityOperationsService)->processDueNotifications(now());

        $this->assertCount(1, $created);
        $this->assertDatabaseHas('domain_notifications', ['mobility_id' => $mobility->id, 'type' => 'pocket_money_transfer']);
        $mobility->iban = 'ES123';
        $this->expectException(ValidationException::class);
        (new MobilityOperationsService)->validateSelectedParticipantBanking($mobility);
    }

    public function test_disciplinary_exclusion_withdrawal_and_teacher_report_reminders_affect_workflow(): void
    {
        $application = $this->application();
        DisciplinaryIncident::create(['application_id' => $application->id, 'severity' => 'serious', 'description' => 'Amonestación', 'occurred_at' => now(), 'status' => 'resolved', 'excludes_from_resolution' => true]);
        $this->assertTrue($application->hasExcludingDisciplinaryIncident());
        $this->assertSame(0, (new ApplicationScoringService)->resolveCall($application->call)['admitted_count']);

        $mobility = $this->mobility($application, ['participant_role' => 'accompanying_teacher', 'end_date' => now()->addDay()]);
        $created = (new MobilityOperationsService)->processDueNotifications(now());
        $this->assertTrue(collect($created)->contains('type', 'final_report_reminder'));

        (new MobilityOperationsService)->processWithdrawal($mobility, ['occurred_at' => now(), 'after_expenses' => true, 'force_majeure' => true, 'reimbursable_expenses' => 25, 'funds_to_return' => 0]);
        $this->assertSame(MobilityStatus::Cancelled, $mobility->fresh()->status);
        $this->assertSame(ApplicationStatus::Withdrawn, $application->fresh()->status);
    }

    private function application(array $overrides = []): Application
    {
        $center = EducationalCenter::create(['code' => fake()->unique()->numerify('########'), 'name' => 'IES Test', 'city' => 'Valencia', 'coordinator_name' => 'Coord', 'coordinator_email' => fake()->unique()->safeEmail()]);
        $project = ErasmusProject::create(['project_code' => fake()->unique()->bothify('KA###'), 'title' => 'Proyecto', 'call_year' => '2026', 'academic_year' => '2026-27', 'start_date' => now(), 'end_date' => now()->addYear(), 'total_grant_awarded' => 1]);
        $call = MobilityCall::create(['erasmus_project_id' => $project->id, 'title' => 'Convocatoria', 'mobility_type' => MobilityType::VetStudentShort, 'academic_year' => '2026-27', 'application_start_date' => now(), 'application_end_date' => now()->addDay(), 'total_vacancies' => 1, 'status' => 'open']);

        return Application::create(array_replace(['mobility_call_id' => $call->id, 'educational_center_id' => $center->id, 'first_name' => 'Ana', 'last_name' => 'Pérez', 'id_document_number' => fake()->unique()->numerify('########A'), 'email' => fake()->unique()->safeEmail(), 'phone' => '600000001', 'vocational_program' => 'Sanidad', 'participant_role' => 'student', 'academic_score' => 10, 'language_score' => 5, 'faculty_report_score' => 5, 'status' => ApplicationStatus::Submitted], $overrides));
    }

    private function mobility(Application $application, array $overrides = []): Mobility
    {
        return Mobility::create(array_replace(['erasmus_project_id' => $application->call->erasmus_project_id, 'mobility_call_id' => $application->mobility_call_id, 'application_id' => $application->id, 'educational_center_id' => $application->educational_center_id, 'participant_name' => $application->full_name, 'participant_email' => $application->email, 'participant_type' => 'student', 'participant_role' => 'student', 'destination_country' => 'IT', 'destination_city' => 'Roma', 'start_date' => now()->addDays(10), 'end_date' => now()->addDays(20), 'duration_days' => 10, 'daily_grant_rate' => 1, 'individual_support_amount' => 1, 'travel_amount' => 1, 'inclusion_amount' => 0, 'total_grant_amount' => 100, 'status' => MobilityStatus::Planned], $overrides));
    }
}
