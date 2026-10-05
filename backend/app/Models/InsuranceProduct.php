<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InsuranceProduct extends Model
{
    protected $fillable = [
        'financial_institution_id',
        'name',
        'slug',
        'insurance_type',
        'description',
        'premium_rate',
        'minimum_premium',
        'maximum_cover',
        'coverage_period_months',
        'covered_risks',
        'eligibility',
        'required_documents',
        'online_application',
        'featured',
        'active',
    ];

    protected $casts = [
        'covered_risks' => 'array',
        'eligibility' => 'array',
        'required_documents' => 'array',
        'online_application' => 'boolean',
        'featured' => 'boolean',
        'active' => 'boolean',
        'premium_rate' => 'decimal:4',
        'minimum_premium' => 'decimal:2',
        'maximum_cover' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name) . '-' . Str::random(4);
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(FinancialInstitution::class, 'financial_institution_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(InsuranceApplication::class);
    }
}
