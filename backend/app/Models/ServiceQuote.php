<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceQuote extends Model
{
    use HasFactory;

    protected $fillable = [

        'service_id',

        'customer_id',

        'provider_id',

        'quantity',

        'unit',

        'quoted_price',

        'message',

        'status',

    ];

    protected $casts = [

        'quoted_price' => 'decimal:2',

        'quantity' => 'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function booking()
    {
        return $this->hasOne(ServiceBooking::class);
    }
}