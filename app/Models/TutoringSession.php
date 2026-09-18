<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutoringSession extends Model
{
    protected $fillable = ['mobility_id', 'tutor_scope', 'mode', 'held_at', 'content', 'tutor_id'];

    protected $casts = ['held_at' => 'datetime'];

    public function mobility(): BelongsTo
    {
        return $this->belongsTo(Mobility::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}
