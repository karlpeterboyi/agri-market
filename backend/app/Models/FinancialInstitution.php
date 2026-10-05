<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialInstitution extends Model
{
    protected $fillable = [

        'owner_user_id',

        'financial_institution_category_id',

        'institution_type',

        'name',

        'slug',

        'short_name',

        'description',

        'logo',

        'website',

        'email',

        'phone',

        'contact_person',

        'head_office',

        'country',

        'regions',

        'offers_online_application',

        'verified',

        'featured',

        'active',
    ];

    protected $casts = [

        'regions' => 'array',

        'offers_online_application' => 'boolean',

        'verified' => 'boolean',

        'featured' => 'boolean',

        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            FinancialInstitutionCategory::class
        );
    }

    /**
     * Future relationship.
     */
    public function loanProducts(): HasMany
    {
        return $this->hasMany(LoanProduct::class);
    }

    public function insuranceProducts(): HasMany
    {
        return $this->hasMany(InsuranceProduct::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}