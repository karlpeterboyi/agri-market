<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsRequest extends Model
{
    protected $fillable = [
        'order_id',
        'listing_id',
        'buyer_id',
        'pickup_region',
        'pickup_district',
        'delivery_region',
        'delivery_district',
        'quantity',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function listing()
    {
        return $this->belongsTo(ProductListing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function assignment()
    {
        return $this->hasOne(TransportAssignment::class);
    }

    public function transportAssignment()
    {
        return $this->hasOne(TransportAssignment::class);
    }
}