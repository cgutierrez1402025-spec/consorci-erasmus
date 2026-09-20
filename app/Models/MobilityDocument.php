<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityDocument extends Model
{
    public const STATUS_PENDING_DELIVERY = 'pending_delivery';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_NOT_APPLICABLE = 'not_applicable';

    protected $fillable = [
        'mobility_id', 'phase', 'document_type', 'label', 'original_filename', 'storage_path',
        'validation_status', 'coordinator_observations', 'submitted_at', 'validated_at', 'validated_by',
    ];

    protected $casts = ['submitted_at' => 'datetime', 'validated_at' => 'datetime'];

    protected static function booted(): void
    {
        static::saving(function (self $document): void {
            if ($document->isDirty('storage_path') && $document->storage_path) {
                $document->submitted_at ??= now();
                if ($document->validation_status === self::STATUS_PENDING_DELIVERY) {
                    $document->validation_status = self::STATUS_IN_REVIEW;
                }
            }

            if ($document->isDirty('validation_status') && in_array($document->validation_status, [self::STATUS_VALIDATED, self::STATUS_REJECTED], true)) {
                $document->validated_at = now();
                $document->validated_by ??= auth()->id();
            }
        });
    }

    public function mobility(): BelongsTo
    {
        return $this->belongsTo(Mobility::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
