<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBankLink extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'bank_code',
        'account_number',
        'account_name',
        'nmb_account_id',
        'nmb_customer_id',
        'verified',
        'meta',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
