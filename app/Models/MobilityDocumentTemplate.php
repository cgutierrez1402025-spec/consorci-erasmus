<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilityDocumentTemplate extends Model
{
    public const PHASE_PRE = 'PRE';

    public const PHASE_DURING = 'DURANTE';

    public const PHASE_POST = 'POST';

    protected $fillable = ['profile', 'phase', 'document_type', 'label', 'required'];

    protected $casts = ['required' => 'boolean'];
}
