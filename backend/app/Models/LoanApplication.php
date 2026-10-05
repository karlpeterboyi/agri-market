<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanApplication extends Model
{
    protected $fillable = [

        'loan_product_id',

        'user_id',

        'application_number',

        'requested_amount',

        'repayment_period_months',

        'purpose',

        'annual_income',

        'farm_size',

        'farm_size_unit',

        'remarks',

        'status',

        'submitted_at',

        'approved_at',

        'rejected_at',
    ];

    protected $casts = [

        'requested_amount' => 'decimal:2',

        'annual_income' => 'decimal:2',

        'farm_size' => 'decimal:2',

        'submitted_at' => 'datetime',

        'approved_at' => 'datetime',

        'rejected_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            LoanProduct::class,
            'loan_product_id'
        );
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
    
    public function documents()
{
    return $this->hasMany(
        LoanApplicationDocument::class
    );
}

public function guarantors()
{
    return $this->hasMany(
        LoanGuarantor::class
    );
}

public function collaterals()
{
    return $this->hasMany(
        LoanCollateral::class
    );
}

public function repayments()
{
    return $this->hasMany(
        LoanRepayment::class
    );
}

public function disbursements()
{
    return $this->hasMany(
        LoanDisbursement::class
    );
}

public function workflowLogs()
{
    return $this->hasMany(
        LoanWorkflowLog::class
    )->latest();
}

}