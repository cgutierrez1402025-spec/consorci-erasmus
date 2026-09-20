<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $application): void {
            $application->iban = $application->iban ? strtoupper(str_replace([' ', '-'], '', $application->iban)) : null;
            $application->calculateTotalScore();
        });
        static::created(fn (self $application) => $application->syncRequiredDocuments());
        static::updated(function (self $application): void {
            if ($application->wasChanged(['vocational_program', 'participant_role', 'is_minor', 'training_branch'])) {
                $application->syncRequiredDocuments();
            }
        });
    }

    protected $fillable = [
        'mobility_call_id',
        'educational_center_id',
        'first_name',
        'last_name',
        'id_document_type',
        'id_document_number',
        'email',
        'phone',
        'birth_date',
        'birth_place',
        'nationality',
        'address',
        'locality',
        'province',
        'country_preferences',
        'communications_consent',
        'vocational_program',
        'level',
        'participant_role',
        'is_minor',
        'training_branch',
        'iban',
        'bank_account_holder',
        'fewer_opportunities_reason',
        'language_certificate_level',
        'academic_score',
        'language_score',
        'faculty_report_score',
        'interview_score',
        'inclusion_factor_score',
        'total_score',
        'status',
        'cv_path',
        'motivation_letter',
        'notes',
        'absence_count',
        'relevant_language_grade',
        'score_breakdown',
        'tie_break_reason',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'academic_score' => 'decimal:2',
        'language_score' => 'decimal:2',
        'faculty_report_score' => 'decimal:2',
        'interview_score' => 'decimal:2',
        'inclusion_factor_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'status' => ApplicationStatus::class,
        'country_preferences' => 'array',
        'communications_consent' => 'boolean',
        'is_minor' => 'boolean',
        'absence_count' => 'integer',
        'relevant_language_grade' => 'decimal:2',
        'score_breakdown' => 'array',
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(MobilityCall::class, 'mobility_call_id');
    }

    public function educationalCenter(): BelongsTo
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mobility(): HasOne
    {
        return $this->hasOne(Mobility::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function interview(): HasOne
    {
        return $this->hasOne(ApplicationInterview::class);
    }

    public function disciplinaryIncidents(): HasMany
    {
        return $this->hasMany(DisciplinaryIncident::class);
    }

    public function calculateTotalScore(): float
    {
        $rubric = $this->call?->scoringRubric() ?? MobilityCall::defaultScoringRubric();
        $sources = [
            'academic' => (float) $this->academic_score,
            'language' => (float) $this->language_score,
            'faculty' => (float) $this->faculty_report_score,
            'interview' => (float) $this->interview_score,
            'inclusion' => (float) $this->inclusion_factor_score,
        ];
        $breakdown = [];
        foreach ($rubric['criteria'] as $key => $criterion) {
            $breakdown[$key] = round(min((float) $criterion['maximum'], max(0, $sources[$key]) / max(0.01, (float) $criterion['source_maximum']) * (float) $criterion['maximum']), 2);
        }
        $this->score_breakdown = $breakdown;
        $this->total_score = round(array_sum($breakdown), 2);

        return $this->total_score;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function requiredDocumentTypes(): array
    {
        $types = array_merge(ApplicationDocument::baseRequiredTypes(), [
            ApplicationDocument::BANK_ACCOUNT_OWNERSHIP,
            ApplicationDocument::DEPOSIT_RECEIPT,
            ApplicationDocument::FINAL_REPORT,
        ]);
        if ($this->participant_role === 'student' && $this->is_minor) {
            $types[] = ApplicationDocument::MINOR_AUTHORIZATION;
        }
        if ($this->training_branch === 'SOCIO_SANITARIA' || ApplicationDocument::requiresCriminalRecordCertificate($this->vocational_program)) {
            $types[] = ApplicationDocument::CRIMINAL_BACKGROUND_CERTIFICATE;
            $types[] = ApplicationDocument::SEXUAL_OFFENCES_CERTIFICATE;
        }

        return array_values(array_unique($types));
    }

    public function documentationComplete(): bool
    {
        $required = $this->requiredDocumentTypes();

        return $this->documents()
            ->whereIn('document_type', $required)
            ->where('status', ApplicationDocument::STATUS_VALIDATED)
            ->count() === count($required);
    }

    public function syncRequiredDocuments(): void
    {
        foreach ($this->requiredDocumentTypes() as $type) {
            $this->documents()->firstOrCreate(['document_type' => $type], ['status' => ApplicationDocument::STATUS_PENDING]);
        }
    }

    public function hasExcludingDisciplinaryIncident(): bool
    {
        return $this->disciplinaryIncidents()->where('excludes_from_resolution', true)->where('status', '!=', 'dismissed')->exists();
    }
}
