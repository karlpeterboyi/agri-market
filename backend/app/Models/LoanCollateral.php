<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanCollateral extends Model
{
    protected $fillable = [

        'loan_application_id',

        'collateral_type',

        'title',

        'description',

        'estimated_value',

        'ownership_reference',

        'location',

        'supporting_documents',

        'verified',

        'verified_at',

        'verified_by',

        'verification_notes',
    ];

    protected $casts = [

        'estimated_value' => 'decimal:2',

        'supporting_documents' => 'array',

        'verified' => 'boolean',

        'verified_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            LoanApplication::class
        );
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }
}