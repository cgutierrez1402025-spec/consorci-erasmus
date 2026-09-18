<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\MobilityType;
use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\EducationalCenter;
use App\Models\ErasmusProject;
use App\Models\MobilityCall;
use App\Models\User;
use App\Services\ApplicationScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_application_is_scored_on_a_hundred_point_rubric_and_requires_fifty_points(): void
    {
        $call = $this->mobilityCall();
        $eligible = $this->application($call, ['academic_score' => 10, 'language_score' => 0, 'faculty_report_score' => 0], 8);
        $ineligible = $this->application($call, ['academic_score' => 10, 'language_score' => 0, 'faculty_report_score' => 0], 7.6);

        $service = new ApplicationScoringService;
        $service->scoreApplication($eligible);
        $service->scoreApplication($ineligible);

        $this->assertSame('50.00', $eligible->fresh()->total_score);
        $this->assertSame(ApplicationStatus::Scored, $eligible->fresh()->status);
        $this->assertSame('49.00', $ineligible->fresh()->total_score);
        $this->assertSame(ApplicationStatus::Ineligible, $ineligible->fresh()->status);
        $this->assertSame(100, array_sum(array_column($call->scoringRubric()['criteria'], 'maximum')));
    }

    public function test_resolution_only_assigns_eligible_candidates_and_places_remaining_ones_on_reserve(): void
    {
        $call = $this->mobilityCall(['total_vacancies' => 1]);
        $admitted = $this->application($call, ['academic_score' => 10, 'language_score' => 5, 'faculty_report_score' => 5], 10);
        $reserve = $this->application($call, ['academic_score' => 9, 'language_score' => 5, 'faculty_report_score' => 5], 10);
        $blocked = Application::create($this->attributes($call, ['academic_score' => 10, 'language_score' => 5, 'faculty_report_score' => 5]));

        $result = (new ApplicationScoringService)->resolveCall($call);

        $this->assertSame(1, $result['admitted_count']);
        $this->assertSame(1, $result['reserve_count']);
        $this->assertSame(ApplicationStatus::Admitted, $admitted->fresh()->status);
        $this->assertSame(ApplicationStatus::Reserve, $reserve->fresh()->status);
        $this->assertSame(ApplicationStatus::Submitted, $blocked->fresh()->status);
        $preparation = collect((new ApplicationScoringService)->preparation($call))->firstWhere('application_id', $blocked->id);
        $this->assertNotEmpty($preparation['blocks']);
    }

    public function test_faculty_assessment_breaks_a_total_score_tie(): void
    {
        [$winner, $other] = $this->tieCandidates(
            ['academic_score' => 10, 'language_score' => 0, 'faculty_report_score' => 5],
            ['academic_score' => 10, 'language_score' => 1, 'faculty_report_score' => 4]
        );
        $this->assertTieWinner($winner, $other, 'equipo docente');
    }

    public function test_lower_absence_count_breaks_a_total_score_tie(): void
    {
        [$winner, $other] = $this->tieCandidates(['absence_count' => 1], ['absence_count' => 2]);
        $this->assertTieWinner($winner, $other, 'faltas de asistencia');
    }

    public function test_relevant_language_grade_breaks_a_total_score_tie(): void
    {
        [$winner, $other] = $this->tieCandidates(['relevant_language_grade' => 8], ['relevant_language_grade' => 7]);
        $this->assertTieWinner($winner, $other, 'idioma relevante');
    }

    public function test_academic_record_breaks_a_total_score_tie(): void
    {
        [$winner, $other] = $this->tieCandidates(
            ['academic_score' => 10, 'language_score' => 0],
            ['academic_score' => 9, 'language_score' => 0.75]
        );
        $this->assertTieWinner($winner, $other, 'expediente académico');
    }

    private function assertTieWinner(Application $winner, Application $other, string $reason): void
    {
        (new ApplicationScoringService)->resolveCall($winner->call);
        $this->assertSame(ApplicationStatus::Admitted, $winner->fresh()->status);
        $this->assertSame(ApplicationStatus::Reserve, $other->fresh()->status);
        $this->assertStringContainsString($reason, $other->fresh()->tie_break_reason);
    }

    private function tieCandidates(array $winner = [], array $other = []): array
    {
        $call = $this->mobilityCall(['total_vacancies' => 1]);
        $base = ['academic_score' => 10, 'language_score' => 0, 'faculty_report_score' => 5, 'absence_count' => 1, 'relevant_language_grade' => 8];

        return [
            $this->application($call, array_replace($base, $winner), 8),
            $this->application($call, array_replace($base, $other), 8),
        ];
    }

    private function mobilityCall(array $attributes = []): MobilityCall
    {
        $project = ErasmusProject::create([
            'project_code' => 'TEST-2024-KA121', 'title' => 'Test Project', 'call_year' => '2024',
            'academic_year' => '2024-2025', 'start_date' => '2024-09-01', 'end_date' => '2025-08-31', 'total_grant_awarded' => 50000,
        ]);

        return MobilityCall::create(array_replace([
            'erasmus_project_id' => $project->id, 'title' => 'Convocatoria Test', 'mobility_type' => MobilityType::VetStudentShort,
            'academic_year' => '2024-2025', 'application_start_date' => '2024-10-01', 'application_end_date' => '2024-10-31',
            'total_vacancies' => 1, 'status' => 'open', 'scoring_rubric' => MobilityCall::defaultScoringRubric(),
        ], $attributes));
    }

    private function application(MobilityCall $call, array $scores, float $interviewScore): Application
    {
        $application = Application::create($this->attributes($call, $scores));
        $application->documents()->update(['status' => 'validated', 'validated_at' => now()]);
        $criteria = collect(ApplicationInterview::criteriaLabels())->map(fn ($_label, $criterion) => [
            'criterion' => $criterion, 'score' => $interviewScore, 'comment' => 'Valoración documentada.',
        ])->values()->all();
        ApplicationInterview::create([
            'application_id' => $application->id, 'interviewed_at' => now(), 'evaluator_id' => User::factory()->create()->id,
            'status' => ApplicationInterview::STATUS_VALIDATED, 'certified_languages' => 'B2', 'selection_reason' => 'Adecuada',
            'criteria' => $criteria,
        ])->syncApplicationScore();

        return $application->fresh();
    }

    private function attributes(MobilityCall $call, array $scores = []): array
    {
        $center = EducationalCenter::firstOrCreate(['code' => '46000001'], ['name' => 'IES Test', 'city' => 'Valencia', 'coordinator_name' => 'Coord Test', 'coordinator_email' => 'coord@test.com']);

        return array_replace([
            'mobility_call_id' => $call->id, 'educational_center_id' => $center->id, 'first_name' => 'Candidato', 'last_name' => fake()->unique()->lastName(),
            'id_document_number' => fake()->unique()->numerify('########A'), 'email' => fake()->unique()->safeEmail(), 'phone' => '600000001',
            'vocational_program' => 'SMR', 'academic_score' => 10, 'language_score' => 0, 'faculty_report_score' => 5,
            'inclusion_factor_score' => 0, 'absence_count' => 1, 'relevant_language_grade' => 8, 'status' => ApplicationStatus::Submitted,
        ], $scores);
    }
}
