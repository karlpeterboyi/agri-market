<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'machinery_listing_id',
        'customer_id',
        'owner_id',
        'start_date',
        'end_date',
        'total_price',
        'payment_status',
        'status',
        'operator_required',
        'notes'
    ];

    protected $casts = [
        'operator_required' => 'boolean',
        'total_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function listing()
    {
        return $this->belongsTo(MachineryListing::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}