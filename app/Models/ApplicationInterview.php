<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationInterview extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_VALIDATED = 'validated';

    protected $fillable = [
        'application_id', 'interviewed_at', 'evaluator_id', 'status', 'certified_languages',
        'selection_reason', 'preferred_countries', 'observations', 'relevant_information', 'criteria',
    ];

    protected $casts = ['interviewed_at' => 'date', 'preferred_countries' => 'array', 'criteria' => 'array'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public static function criteriaLabels(): array
    {
        return [
            'motivation' => 'Motivación',
            'obligations' => 'Obligaciones laborales, sociales y de convivencia',
            'travel_and_cultures' => 'Viajes y valoración de otras culturas',
            'family_support' => 'Opinión y apoyo familiar',
            'study_continuity' => 'Continuidad de estudios',
            'work_experience' => 'Experiencia laboral vinculada al ciclo',
            'teamwork' => 'Experiencia de trabajo en equipo',
            'dissemination_commitment' => 'Compromiso de difusión, WhatsApp y redes',
        ];
    }

    public function calculatedScore(): float
    {
        $scores = collect($this->criteria ?? [])->pluck('score')->filter(fn ($score) => is_numeric($score));

        return $scores->isEmpty() ? 0.0 : round((float) $scores->avg(), 2);
    }

    public function syncApplicationScore(): void
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            return;
        }

        $application = $this->application;
        $application->interview_score = $this->calculatedScore();
        $application->calculateTotalScore();
        $application->save();
    }

    protected static function booted(): void
    {
        static::saving(function (self $interview): void {
            if (in_array($interview->status, [self::STATUS_COMPLETED, self::STATUS_VALIDATED], true)) {
                $criteria = collect($interview->criteria ?? []);
                $required = array_keys(self::criteriaLabels());
                if ($criteria->count() !== count($required)
                    || $criteria->pluck('criterion')->sort()->values()->all() !== collect($required)->sort()->values()->all()
                    || $criteria->contains(fn ($criterion) => ! isset($criterion['score'], $criterion['comment']) || ! is_numeric($criterion['score']) || $criterion['score'] < 0 || $criterion['score'] > 10)) {
                    throw new \DomainException('Una entrevista realizada o validada debe contener todos los criterios, puntuados de 0 a 10 y comentados.');
                }
            }
        });
    }
}
