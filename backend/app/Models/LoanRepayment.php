<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    protected $fillable = [

        'loan_application_id',

        'installment_number',

        'due_date',

        'principal_amount',

        'interest_amount',

        'penalty_amount',

        'total_amount',

        'amount_paid',

        'balance',

        'status',

        'paid_at',

        'transaction_reference',
    ];

    protected $casts = [

        'due_date' => 'date',

        'paid_at' => 'datetime',

        'principal_amount' => 'decimal:2',

        'interest_amount' => 'decimal:2',

        'penalty_amount' => 'decimal:2',

        'total_amount' => 'decimal:2',

        'amount_paid' => 'decimal:2',

        'balance' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            LoanApplication::class
        );
    }
}