<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        "mobility_call_id",
        "educational_center_id",
        "first_name",
        "last_name",
        "id_document_type",
        "id_document_number",
        "email",
        "phone",
        "birth_date",
        "vocational_program",
        "level",
        "language_certificate_level",
        "academic_score",
        "language_score",
        "faculty_report_score",
        "interview_score",
        "inclusion_factor_score",
        "total_score",
        "status",
        "cv_path",
        "motivation_letter",
        "notes",
    ];

    protected $casts = [
        "birth_date" => "date",
        "academic_score" => "decimal:2",
        "language_score" => "decimal:2",
        "faculty_report_score" => "decimal:2",
        "interview_score" => "decimal:2",
        "inclusion_factor_score" => "decimal:2",
        "total_score" => "decimal:2",
        "status" => ApplicationStatus::class,
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(MobilityCall::class, "mobility_call_id");
    }

    public function educationalCenter(): BelongsTo
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function mobility(): HasOne
    {
        return $this->hasOne(Mobility::class);
    }

    public function calculateTotalScore(): float
    {
        $total = (float) $this->academic_score
            + (float) $this->language_score
            + (float) $this->faculty_report_score
            + (float) $this->interview_score
            + (float) $this->inclusion_factor_score;

        $this->total_score = round($total, 2);
        return $this->total_score;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
