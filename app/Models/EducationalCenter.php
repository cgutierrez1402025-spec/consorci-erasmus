<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        "code",
        "name",
        "city",
        "province",
        "coordinator_name",
        "coordinator_email",
        "coordinator_phone",
        "is_active",
        "notes",
    ];

    protected $casts = [
        "is_active" => "boolean",
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function mobilities(): HasMany
    {
        return $this->hasMany(Mobility::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
