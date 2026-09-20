<?php

namespace App\Models;

use App\Enums\CountryGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vat_number',
        'country_code',
        'country_group',
        'city',
        'address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website',
        'sector',
        'company_size',
        'offers_allowance',
        'working_languages',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'country_group' => CountryGroup::class,
        'working_languages' => 'array',
        'is_active' => 'boolean',
        'offers_allowance' => 'boolean',
    ];

    public function mobilities(): HasMany
    {
        return $this->hasMany(Mobility::class);
    }
}
