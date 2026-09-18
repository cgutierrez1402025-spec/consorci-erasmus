<?php

namespace App\Models;

use App\Enums\MobilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mobility extends Model
{
    use HasFactory;

    protected $fillable = [
        "erasmus_project_id",
        "mobility_call_id",
        "application_id",
        "host_partner_id",
        "educational_center_id",
        "participant_name",
        "participant_email",
        "participant_type",
        "destination_country",
        "destination_city",
        "start_date",
        "end_date",
        "duration_days",
        "travel_type",
        "fewer_opportunities",
        "daily_grant_rate",
        "individual_support_amount",
        "travel_amount",
        "inclusion_amount",
        "total_grant_amount",
        "status",
        "learning_agreement_signed",
        "grant_agreement_signed",
        "certificate_of_attendance",
        "eu_survey_completed",
        "tutor_in_host",
        "tutor_in_origin",
        "notes",
    ];

    protected $casts = [
        "start_date" => "date",
        "end_date" => "date",
        "duration_days" => "integer",
        "fewer_opportunities" => "boolean",
        "learning_agreement_signed" => "boolean",
        "grant_agreement_signed" => "boolean",
        "certificate_of_attendance" => "boolean",
        "eu_survey_completed" => "boolean",
        "daily_grant_rate" => "decimal:2",
        "individual_support_amount" => "decimal:2",
        "travel_amount" => "decimal:2",
        "inclusion_amount" => "decimal:2",
        "total_grant_amount" => "decimal:2",
        "status" => MobilityStatus::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ErasmusProject::class, "erasmus_project_id");
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(MobilityCall::class, "mobility_call_id");
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function hostPartner(): BelongsTo
    {
        return $this->belongsTo(HostPartner::class);
    }

    public function educationalCenter(): BelongsTo
    {
        return $this->belongsTo(EducationalCenter::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MobilityPayment::class);
    }
}
