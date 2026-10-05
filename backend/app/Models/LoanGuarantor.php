<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanGuarantor extends Model
{
    protected $fillable = [

        'loan_application_id',

        'full_name',

        'national_id',

        'phone',

        'email',

        'relationship',

        'occupation',

        'employer',

        'annual_income',

        'physical_address',

        'accepted',

        'accepted_at',

        'remarks',
    ];

    protected $casts = [

        'annual_income' => 'decimal:2',

        'accepted' => 'boolean',

        'accepted_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            LoanApplication::class,
            'loan_application_id'
        );
    }
}