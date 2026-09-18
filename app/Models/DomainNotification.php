<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainNotification extends Model
{
    protected $fillable = ['mobility_id', 'type', 'scheduled_for', 'message', 'status'];

    protected $casts = ['scheduled_for' => 'date'];

    public function mobility(): BelongsTo
    {
        return $this->belongsTo(Mobility::class);
    }
}
