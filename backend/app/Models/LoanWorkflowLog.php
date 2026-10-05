<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanWorkflowLog extends Model
{
    protected $fillable = [

        'loan_application_id',

        'user_id',

        'action',

        'from_status',

        'to_status',

        'remarks',

        'metadata',

    ];

    protected $casts = [

        'metadata' => 'array',

    ];

    public function loanApplication()
    {
        return $this->belongsTo(
            LoanApplication::class
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}