<?php

namespace Tests\Feature;

use App\Enums\MobilityStatus;
use App\Enums\MobilityType;
use App\Models\EducationalCenter;
use App\Models\ErasmusProject;
use App\Models\Mobility;
use App\Models\MobilityCall;
use App\Models\User;
use App\Services\MobilityChecklistService;
use App\Services\MobilityComplianceService;
use Database\Seeders\MobilityDocumentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobilityChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_mobility_receives_the_document_checklist_for_its_program(): void
    {
        $this->seed(MobilityDocumentTemplateSeeder::class);
        $mobility = $this->mobility('GM_STEPV');

        $this->assertCount(14, $mobility->documents);
        $this->assertDatabaseHas('mobility_documents', [
            'mobility_id' => $mobility->id,
            'document_type' => 'learning_agreement',
            'phase' => 'PRE',
        ]);
        $this->assertSame(0, (new MobilityChecklistService)->completionPercentage($mobility));
    }

    public function test_authorized_admin_can_download_private_mobility_document(): void
    {
        $this->seed(MobilityDocumentTemplateSeeder::class);
        $mobility = $this->mobility('GM_STEPV');
        $document = $mobility->documents()->first();
        Storage::fake('private');
        Storage::disk('private')->put('documentos/test.pdf', 'private-content');
        $document->update(['storage_path' => 'documentos/test.pdf', 'original_filename' => 'test.pdf']);

        $response = $this->actingAs($mobility->user)->get(route('mobility-documents.download', $document));

        $response->assertOk();
        $response->assertDownload('test.pdf');
    }

    public function test_compliance_service_creates_a_pre_mobility_alert_for_pending_documents(): void
    {
        $this->seed(MobilityDocumentTemplateSeeder::class);
        $mobility = $this->mobility('GM_STEPV', ['start_date' => now()->addDays(7)]);

        $created = (new MobilityComplianceService)->processDueChecks(now());

        $this->assertCount(1, $created);
        $this->assertDatabaseHas('domain_notifications', ['mobility_id' => $mobility->id, 'type' => 'pre_documents_due']);
    }

    private function mobility(string $programType, array $overrides = []): Mobility
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $center = EducationalCenter::create([
            'code' => fake()->unique()->numerify('########'),
            'name' => 'IES Sant Vicent Ferrer',
            'city' => 'Algemesí',
            'coordinator_name' => 'Coordinación Erasmus',
            'coordinator_email' => fake()->unique()->safeEmail(),
        ]);
        $project = ErasmusProject::create([
            'project_code' => fake()->unique()->bothify('KA###'),
            'title' => 'Proyecto ECHE',
            'call_year' => '2026',
            'academic_year' => '2026-2027',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_grant_awarded' => 1000,
        ]);
        $call = MobilityCall::create([
            'erasmus_project_id' => $project->id,
            'title' => 'Convocatoria de prueba',
            'mobility_type' => MobilityType::VetStudentShort,
            'program_type' => $programType,
            'academic_year' => '2026-2027',
            'application_start_date' => now(),
            'application_end_date' => now()->addDay(),
            'total_vacancies' => 1,
            'status' => 'open',
        ]);

        return Mobility::create(array_replace([
            'erasmus_project_id' => $project->id,
            'mobility_call_id' => $call->id,
            'user_id' => $user->id,
            'educational_center_id' => $center->id,
            'participant_name' => $user->name,
            'participant_email' => $user->email,
            'participant_type' => 'student',
            'destination_country' => 'IT',
            'destination_city' => 'Roma',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'duration_days' => 10,
            'daily_grant_rate' => 10,
            'individual_support_amount' => 100,
            'travel_amount' => 100,
            'inclusion_amount' => 0,
            'total_grant_amount' => 200,
            'status' => MobilityStatus::Planned,
        ], $overrides));
    }
}
