<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportQuote extends Model
{
    protected $fillable = [
        'order_id', 'transporter_id', 'vehicle_id',
        'distance_km', 'price_per_km', 'total_cost',
        'pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng', 'status',
    ];

    protected $casts = [
        'distance_km' => 'float',
        'price_per_km' => 'float',
        'total_cost' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
