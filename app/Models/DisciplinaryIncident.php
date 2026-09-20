<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryIncident extends Model
{
    protected $fillable = ['application_id', 'severity', 'description', 'occurred_at', 'instructor_id', 'sanction', 'status', 'excludes_from_resolution'];

    protected $casts = ['occurred_at' => 'date', 'excludes_from_resolution' => 'boolean'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
