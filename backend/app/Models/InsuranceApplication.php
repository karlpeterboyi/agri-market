<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceApplication extends Model
{
    protected $fillable = [
        'insurance_product_id',
        'applicant_id',
        'sum_insured',
        'premium_amount',
        'status',
        'farm_details',
        'notes',
        'submitted_at',
        'decided_at',
    ];

    protected $casts = [
        'farm_details' => 'array',
        'sum_insured' => 'decimal:2',
        'premium_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InsuranceProduct::class, 'insurance_product_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
