<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityWithdrawal extends Model
{
    protected $fillable = ['mobility_id', 'occurred_at', 'after_expenses', 'force_majeure', 'reimbursable_expenses', 'funds_to_return', 'notes', 'processed_by'];

    protected $casts = ['occurred_at' => 'date', 'after_expenses' => 'boolean', 'force_majeure' => 'boolean', 'reimbursable_expenses' => 'decimal:2', 'funds_to_return' => 'decimal:2'];

    public function mobility(): BelongsTo
    {
        return $this->belongsTo(Mobility::class);
    }
}
