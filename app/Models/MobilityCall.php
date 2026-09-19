<?php

namespace App\Models;

use App\Enums\MobilityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobilityCall extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $call): void {
            if ($call->scoring_rubric === null) {
                return;
            }

            $rubric = $call->scoringRubric();
            $maximum = array_sum(array_column($rubric['criteria'], 'maximum'));
            if ($maximum > 100 || (float) $rubric['minimum_score'] !== 50.0) {
                throw new \DomainException('La rúbrica no puede superar 100 puntos y el mínimo de participación es 50.');
            }
        });
    }

    protected $fillable = [
        'erasmus_project_id',
        'title',
        'mobility_type',
        'program_type',
        'academic_year',
        'application_start_date',
        'application_end_date',
        'provisional_list_date',
        'final_list_date',
        'total_vacancies',
        'status',
        'requirements',
        'scoring_rubric',
    ];

    protected $casts = [
        'mobility_type' => MobilityType::class,
        'application_start_date' => 'date',
        'application_end_date' => 'date',
        'provisional_list_date' => 'date',
        'final_list_date' => 'date',
        'total_vacancies' => 'integer',
        'scoring_rubric' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ErasmusProject::class, 'erasmus_project_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function mobilities(): HasMany
    {
        return $this->hasMany(Mobility::class);
    }

    public function scoringRubric(): array
    {
        return array_replace_recursive(self::defaultScoringRubric(), $this->scoring_rubric ?? []);
    }

    public static function defaultScoringRubric(): array
    {
        return [
            'minimum_score' => 50,
            'criteria' => [
                'academic' => ['label' => 'Expediente académico', 'maximum' => 30, 'source_maximum' => 10],
                'language' => ['label' => 'Idioma acreditado', 'maximum' => 20, 'source_maximum' => 5],
                'faculty' => ['label' => 'Valoración del equipo docente', 'maximum' => 20, 'source_maximum' => 5],
                'interview' => ['label' => 'Entrevista', 'maximum' => 25, 'source_maximum' => 10],
                'inclusion' => ['label' => 'Inclusión / menos oportunidades', 'maximum' => 5, 'source_maximum' => 1],
            ],
        ];
    }
}
