<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\ProductListing;
class Order extends Model
{
    // Status Constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAID = 'paid';
    public const STATUS_IN_ESCROW = 'in_escrow';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'marketplace_type', 'marketplace_id', 'title', 'platform_fee', 'seller_net',
        'offer_id',
        'buyer_id',
        'seller_id',
        'listing_id',
        'quantity',
        'price',
        'total_amount',
        'status',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    
    public function items()
{
    return $this->hasMany(OrderItem::class);
}

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function listing()
    {
        return $this->belongsTo(ProductListing::class, 'listing_id');
    }
    
    public function logisticsRequest()
{
    return $this->hasOne(LogisticsRequest::class);
}

public function payment()
{
    return $this->hasOne(Payment::class);
}
    
}
