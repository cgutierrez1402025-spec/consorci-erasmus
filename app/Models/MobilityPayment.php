<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        "mobility_id",
        "payment_type",
        "amount",
        "scheduled_date",
        "paid_date",
        "status",
        "reference_number",
        "notes",
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "scheduled_date" => "date",
        "paid_date" => "date",
        "status" => PaymentStatus::class,
    ];

    public function mobility(): BelongsTo
    {
        return $this->belongsTo(Mobility::class);
    }
}
