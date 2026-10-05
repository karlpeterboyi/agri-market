<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditScore extends Model
{
    protected $fillable = [

        'user_id',

        'overall_score',

        'marketplace_score',

        'repayment_score',

        'profile_score',

        'reputation_score',

        'subscription_score',

        'activity_score',

        'risk_level',

        'calculated_at',
    ];

    protected $casts = [

        'calculated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}