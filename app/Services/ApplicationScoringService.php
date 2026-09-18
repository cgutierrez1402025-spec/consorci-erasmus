<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\MobilityCall;

class ApplicationScoringService
{
    /**
     * Baremar una solicitud individual.
     */
    public function scoreApplication(Application $app): Application
    {
        if ($app->interview?->status !== ApplicationInterview::STATUS_VALIDATED) {
            throw new \DomainException('La candidatura no puede baremarse sin una entrevista validada.');
        }

        $app->calculateTotalScore();
        $minimum = $app->call->scoringRubric()['minimum_score'];
        $app->status = $app->total_score >= $minimum ? ApplicationStatus::Scored : ApplicationStatus::Ineligible;
        $app->save();

        return $app;
    }

    /**
     * Procesar la resolución de una convocatoria asignando plazas adjudicadas y reservas según baremo.
     */
    public function resolveCall(MobilityCall $call): array
    {
        $applications = $call->applications()->with(['interview', 'documents'])->get()
            ->each(function (Application $application): void {
                $application->calculateTotalScore();
                $application->save();
            })
            ->filter(fn (Application $app) => $this->isResolvable($app))
            ->sort(fn (Application $left, Application $right) => $this->compare($left, $right))
            ->values();

        $vacancies = $call->total_vacancies;
        $assigned = 0;
        $admitted = [];
        $reserve = [];

        foreach ($applications as $index => $app) {
            $previous = $applications->get($index - 1);
            $app->tie_break_reason = $previous && (float) $previous->total_score === (float) $app->total_score
                ? $this->tieBreakReason($previous, $app)
                : null;
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

        $call->status = 'resolved';
        $call->save();

        return [
            'total_vacancies' => $vacancies,
            'admitted_count' => count($admitted),
            'reserve_count' => count($reserve),
        ];
    }

    public function preparation(MobilityCall $call): array
    {
        return $call->applications()->with(['interview', 'documents'])->get()->map(function (Application $application): array {
            $blocks = [];
            if (in_array($application->status, [ApplicationStatus::Rejected, ApplicationStatus::Withdrawn], true)) {
                $blocks[] = 'Candidatura rechazada o retirada';
            }
            if (! $application->documentationComplete()) {
                $blocks[] = 'Documentación pendiente o no validada';
            }
            if ($application->interview?->status !== ApplicationInterview::STATUS_VALIDATED) {
                $blocks[] = 'Entrevista no validada';
            }
            if ($application->total_score < $application->call->scoringRubric()['minimum_score']) {
                $blocks[] = 'No alcanza el mínimo de 50 puntos';
            }

            return ['application_id' => $application->id, 'name' => $application->full_name, 'blocks' => $blocks];
        })->all();
    }

    private function isResolvable(Application $application): bool
    {
        return ! in_array($application->status, [ApplicationStatus::Rejected, ApplicationStatus::Withdrawn], true)
            && ! $application->hasExcludingDisciplinaryIncident()
            && $application->documentationComplete()
            && $application->interview?->status === ApplicationInterview::STATUS_VALIDATED
            && $application->total_score >= $application->call->scoringRubric()['minimum_score'];
    }

    private function compare(Application $left, Application $right): int
    {
        foreach ([
            [$right->total_score, $left->total_score],
            [$right->faculty_report_score, $left->faculty_report_score],
            [$left->absence_count, $right->absence_count],
            [$right->relevant_language_grade, $left->relevant_language_grade],
            [$right->academic_score, $left->academic_score],
            [$left->id, $right->id],
        ] as [$a, $b]) {
            if ($a <=> $b) {
                return $a <=> $b;
            }
        }

        return 0;
    }

    private function tieBreakReason(Application $higher, Application $lower): string
    {
        foreach ([
            ['valoración del equipo docente', $higher->faculty_report_score, $lower->faculty_report_score],
            ['menor número de faltas de asistencia', $lower->absence_count, $higher->absence_count],
            ['calificación del idioma relevante', $higher->relevant_language_grade, $lower->relevant_language_grade],
            ['expediente académico', $higher->academic_score, $lower->academic_score],
        ] as [$criterion, $left, $right]) {
            if ((float) $left !== (float) $right) {
                return "Empate en puntuación total resuelto por {$criterion}.";
            }
        }

        return 'Empate completo resuelto por identificador de candidatura para un orden técnicamente estable.';
    }
}
