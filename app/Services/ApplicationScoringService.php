<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\MobilityCall;

class ApplicationScoringService
{
    /**
     * Baremar una solicitud individual.
     */
    public function scoreApplication(Application $app): Application
    {
        $app->calculateTotalScore();
        $app->status = ApplicationStatus::Scored;
        $app->save();

        return $app;
    }

    /**
     * Procesar la resolución de una convocatoria asignando plazas adjudicadas y reservas según baremo.
     */
    public function resolveCall(MobilityCall $call): array
    {
        $applications = $call->applications()
            ->orderByDesc("total_score")
            ->orderByDesc("language_score")
            ->orderByDesc("academic_score")
            ->get();

        $vacancies = $call->total_vacancies;
        $assigned = 0;
        $admitted = [];
        $reserve = [];

        foreach ($applications as $app) {
            if ($app->status === ApplicationStatus::Rejected || $app->status === ApplicationStatus::Withdrawn) {
                continue;
            }

            if ($assigned < $vacancies) {
                $app->status = ApplicationStatus::Admitted;
                $assigned++;
                $admitted[] = $app;
            } else {
                $app->status = ApplicationStatus::Reserve;
                $reserve[] = $app;
            }
            $app->save();
        }

        $call->status = "resolved";
        $call->save();

        return [
            "total_vacancies" => $vacancies,
            "admitted_count" => count($admitted),
            "reserve_count" => count($reserve),
        ];
    }
}
