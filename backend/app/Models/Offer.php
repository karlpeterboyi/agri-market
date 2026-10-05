<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'offered_price',
        'quantity',
        'message',
        'status',
    ];

    public function listing()
    {
        return $this->belongsTo(ProductListing::class, 'listing_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}