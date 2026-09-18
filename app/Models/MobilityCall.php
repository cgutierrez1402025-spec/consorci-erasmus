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

    protected $fillable = [
        "erasmus_project_id",
        "title",
        "mobility_type",
        "academic_year",
        "application_start_date",
        "application_end_date",
        "provisional_list_date",
        "final_list_date",
        "total_vacancies",
        "status",
        "requirements",
    ];

    protected $casts = [
        "mobility_type" => MobilityType::class,
        "application_start_date" => "date",
        "application_end_date" => "date",
        "provisional_list_date" => "date",
        "final_list_date" => "date",
        "total_vacancies" => "integer",
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ErasmusProject::class, "erasmus_project_id");
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function mobilities(): HasMany
    {
        return $this->hasMany(Mobility::class);
    }
}
