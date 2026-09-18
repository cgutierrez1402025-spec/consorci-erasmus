<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityBond extends Model
{
    public const DESTINATION_IBAN = 'ES28-3159-0058-0423-0889-0025';

    protected $fillable = ['mobility_id', 'amount', 'concept', 'destination_iban', 'status', 'validated_at', 'validated_by', 'observations'];

    protected $casts = ['amount' => 'decimal:2', 'validated_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(function (self $bond): void {
            $bond->destination_iban = strtoupper(str_replace([' ', '-'], '', $bond->destination_iban));
            if ($bond->status === 'validated' && ((float) $bond->amount !== 275.0 || $bond->destination_iban !== str_replace('-', '', self::DESTINATION_IBAN) || ! str_contains(mb_strtolower($bond->concept), mb_strtolower($bond->mobility->participant_name)))) {
                throw new \DomainException('La fianza validada debe ser de 275 EUR, incluir el nombre completo y dirigirse a la cuenta STEPV indicada.');
            }
        });
    }

    public function mobility(): BelongsTo
    {
        return $this->belongsTo(Mobility::class);
    }
}
