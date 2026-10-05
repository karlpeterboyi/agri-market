<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanDisbursement extends Model
{
    protected $fillable = [

        'loan_application_id',

        'reference_number',

        'approved_amount',

        'disbursed_amount',

        'disbursement_date',

        'channel',

        'account_name',

        'account_number',

        'bank_name',

        'mobile_network',

        'phone_number',

        'transaction_reference',

        'status',

        'notes',
    ];

    protected $casts = [

        'approved_amount' => 'decimal:2',

        'disbursed_amount' => 'decimal:2',

        'disbursement_date' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            LoanApplication::class
        );
    }
}