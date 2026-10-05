<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanProduct extends Model
{
    protected $fillable = [

        'financial_institution_id',

        'name',

        'slug',

        'description',

        'loan_type',

        'minimum_amount',

        'maximum_amount',

        'interest_rate',

        'minimum_duration_months',

        'maximum_duration_months',

        'processing_fee',

        'requires_collateral',

        'online_application',

        'eligibility',

        'required_documents',

        'featured',

        'active',
    ];

    protected $casts = [

        'eligibility' => 'array',

        'required_documents' => 'array',

        'requires_collateral' => 'boolean',

        'online_application' => 'boolean',

        'featured' => 'boolean',

        'active' => 'boolean',

        'minimum_amount' => 'decimal:2',

        'maximum_amount' => 'decimal:2',

        'interest_rate' => 'decimal:2',

        'processing_fee' => 'decimal:2',
    ];

    public function financialInstitution(): BelongsTo
    {
        return $this->belongsTo(
            FinancialInstitution::class,
            'financial_institution_id'
        );
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(
            FinancialInstitution::class,
            'financial_institution_id'
        );
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            LoanApplication::class
        );
    }
}