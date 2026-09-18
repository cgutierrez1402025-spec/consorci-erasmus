<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\EducationalCenter;
use App\Models\ErasmusProject;
use App\Models\MobilityCall;
use App\Services\ApplicationScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_score_calculation_and_call_resolution(): void
    {
        $center = EducationalCenter::create([
            "code" => "46000001",
            "name" => "IES Test",
            "city" => "Valencia",
            "coordinator_name" => "Coord Test",
            "coordinator_email" => "coord@test.com",
        ]);

        $project = ErasmusProject::create([
            "project_code" => "TEST-2024-KA121",
            "title" => "Test Project",
            "call_year" => "2024",
            "academic_year" => "2024-2025",
            "start_date" => "2024-09-01",
            "end_date" => "2025-08-31",
            "total_grant_awarded" => 50000,
        ]);

        $call = MobilityCall::create([
            "erasmus_project_id" => $project->id,
            "title" => "Convocatoria Test",
            "mobility_type" => \App\Enums\MobilityType::VetStudentShort,
            "academic_year" => "2024-2025",
            "application_start_date" => "2024-10-01",
            "application_end_date" => "2024-10-31",
            "total_vacancies" => 1,
            "status" => "open",
        ]);

        $app1 = Application::create([
            "mobility_call_id" => $call->id,
            "educational_center_id" => $center->id,
            "first_name" => "Candidato",
            "last_name" => "Uno",
            "id_document_number" => "11111111A",
            "email" => "uno@test.com",
            "phone" => "600000001",
            "vocational_program" => "SMR",
            "academic_score" => 9.0,
            "language_score" => 3.5,
            "faculty_report_score" => 4.5,
            "interview_score" => 4.5,
            "inclusion_factor_score" => 0.0,
            "status" => ApplicationStatus::Submitted,
        ]);
        $app1->calculateTotalScore();
        $app1->save();

        $app2 = Application::create([
            "mobility_call_id" => $call->id,
            "educational_center_id" => $center->id,
            "first_name" => "Candidato",
            "last_name" => "Dos",
            "id_document_number" => "22222222B",
            "email" => "dos@test.com",
            "phone" => "600000002",
            "vocational_program" => "SMR",
            "academic_score" => 6.0,
            "language_score" => 2.0,
            "faculty_report_score" => 3.0,
            "interview_score" => 3.0,
            "inclusion_factor_score" => 0.0,
            "status" => ApplicationStatus::Submitted,
        ]);
        $app2->calculateTotalScore();
        $app2->save();

        $this->assertEquals(21.5, $app1->total_score);
        $this->assertEquals(14.0, $app2->total_score);

        $service = new ApplicationScoringService();
        $resolution = $service->resolveCall($call);

        $this->assertEquals(1, $resolution["admitted_count"]);
        $this->assertEquals(1, $resolution["reserve_count"]);

        $this->assertEquals(ApplicationStatus::Admitted, $app1->fresh()->status);
        $this->assertEquals(ApplicationStatus::Reserve, $app2->fresh()->status);
    }
}
