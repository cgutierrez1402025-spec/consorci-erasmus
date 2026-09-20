<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationInterview;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoInterviewsSeeder extends Seeder
{
    public function run(): void
    {
        $evaluator = User::query()->where('role', 'superadmin')->firstOrFail();

        $interviews = [
            'marc.gimeno@alumnat.es' => [
                'status' => ApplicationInterview::STATUS_VALIDATED,
                'score' => 8.5,
                'languages' => 'Inglés B2 acreditado y preparación básica de italiano.',
                'reason' => 'Muestra autonomía técnica, motivación y una buena adaptación al entorno internacional.',
                'countries' => ['Italia', 'Alemania'],
            ],
            'laia.pons@alumnat.es' => [
                'status' => ApplicationInterview::STATUS_VALIDATED,
                'score' => 9.0,
                'languages' => 'Inglés B1 acreditado; realiza formación complementaria de conversación.',
                'reason' => 'Destaca por su madurez, compromiso social y clara vinculación con el ciclo sanitario.',
                'countries' => ['Irlanda', 'Portugal'],
            ],
            'pau.navarro@alumnat.es' => [
                'status' => ApplicationInterview::STATUS_VALIDATED,
                'score' => 7.5,
                'languages' => 'Inglés B1 acreditado.',
                'reason' => 'Cuenta con experiencia administrativa y una motivación coherente con la movilidad.',
                'countries' => ['Francia', 'Italia'],
            ],
            'aitana.soriano@alumnat.es' => [
                'status' => ApplicationInterview::STATUS_COMPLETED,
                'score' => 8.0,
                'languages' => 'Inglés B2 acreditado.',
                'reason' => 'Expone objetivos profesionales claros y experiencia positiva de trabajo cooperativo.',
                'countries' => ['Alemania', 'Italia'],
            ],
            'joan.ribera@alumnat.es' => [
                'status' => ApplicationInterview::STATUS_COMPLETED,
                'score' => 6.5,
                'languages' => 'Inglés A2 acreditado.',
                'reason' => 'Necesita reforzar la competencia lingüística, pero manifiesta interés y compromiso.',
                'countries' => ['Francia', 'Portugal'],
            ],
            'carla.montesinos@alumnat.es' => [
                'status' => ApplicationInterview::STATUS_PENDING,
                'score' => null,
                'languages' => null,
                'reason' => null,
                'countries' => [],
            ],
        ];

        $index = 0;
        foreach ($interviews as $email => $data) {
            $application = Application::query()->where('email', $email)->first();

            if (! $application) {
                continue;
            }

            $criteria = $data['score'] === null ? [] : $this->criteria((float) $data['score']);
            $interview = ApplicationInterview::query()->updateOrCreate(
                ['application_id' => $application->id],
                [
                    'interviewed_at' => $data['score'] === null ? null : now()->subDays($index + 2)->toDateString(),
                    'evaluator_id' => $evaluator->id,
                    'status' => $data['status'],
                    'certified_languages' => $data['languages'],
                    'selection_reason' => $data['reason'],
                    'preferred_countries' => $data['countries'],
                    'observations' => $data['score'] === null ? 'Entrevista pendiente de calendario.' : 'Entrevista de demostración para revisión de comisión.',
                    'relevant_information' => $data['score'] === null ? null : 'La candidatura conoce las obligaciones de convivencia y el compromiso de difusión posterior.',
                    'criteria' => $criteria,
                ],
            );

            $interview->syncApplicationScore();
            $index++;
        }
    }

    private function criteria(float $baseScore): array
    {
        return collect(ApplicationInterview::criteriaLabels())
            ->map(function (string $label, string $criterion) use ($baseScore): array {
                return [
                    'criterion' => $criterion,
                    'score' => min(10, $baseScore),
                    'comment' => "{$label}: respuesta valorada favorablemente durante la entrevista.",
                ];
            })
            ->values()
            ->all();
    }
}
