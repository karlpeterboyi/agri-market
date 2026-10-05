<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceBooking extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ON_THE_WAY = 'on_the_way';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Payment Status Constants
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_ESCROW = 'held_in_escrow';
    public const PAYMENT_RELEASED = 'released';
    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'service_quote_id',
        'customer_id',
        'provider_id',
        'booking_reference',
        'booking_date',
        'booking_time',
        'scheduled_date',
        'completed_date',
        'region',
        'district',
        'farm_location',
        'quantity',
        'unit',
        'total_price',
        'status',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'quantity' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function quote()
    {
        return $this->belongsTo(ServiceQuote::class, 'service_quote_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isEscrow()
    {
        return $this->payment_status === self::PAYMENT_ESCROW;
    }

    public function isReleased()
    {
        return $this->payment_status === self::PAYMENT_RELEASED;
    }
}
