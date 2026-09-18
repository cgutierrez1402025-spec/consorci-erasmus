<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErasmusProject extends Model
{
    use HasFactory;

    protected $fillable = [
        "project_code",
        "title",
        "call_year",
        "academic_year",
        "start_date",
        "end_date",
        "total_grant_awarded",
        "individual_support_grant",
        "travel_grant",
        "organizational_support_grant",
        "inclusion_support_grant",
        "status",
        "notes",
    ];

    protected $casts = [
        "start_date" => "date",
        "end_date" => "date",
        "total_grant_awarded" => "decimal:2",
        "individual_support_grant" => "decimal:2",
        "travel_grant" => "decimal:2",
        "organizational_support_grant" => "decimal:2",
        "inclusion_support_grant" => "decimal:2",
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(MobilityCall::class);
    }

    public function mobilities(): HasMany
    {
        return $this->hasMany(Mobility::class);
    }
}
