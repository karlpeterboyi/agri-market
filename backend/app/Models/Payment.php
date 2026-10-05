<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
    'order_id',
    'payer_id',
    'amount',
    'escrow_amount',
    'method',
    'status',
    'transaction_ref',
    'phone',
    'provider_reference',
    'pesapal_order_tracking_id',
    'paid_at',
    'released_at',
    'refunded_at',
    'user_subscription_id',
    'payment_type',
    'course_enrollment_id',
    'meta',
];

    // Relationships moved inside the class boundaries
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
    
    public function subscription()
{
    return $this->belongsTo(
        UserSubscription::class,
        'user_subscription_id'
    );
}


    public function courseEnrollment()
    {
        return $this->belongsTo(\App\Models\CourseEnrollment::class, 'course_enrollment_id');
    }
}
