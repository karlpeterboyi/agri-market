<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportAssignment extends Model
{
    protected $fillable = [
        'logistics_request_id',
        'transporter_id',
        'vehicle_id',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function logisticsRequest()
    {
        return $this->belongsTo(LogisticsRequest::class);
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deliveryUpdates()
    {
        return $this->hasMany(DeliveryUpdate::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            'assigned',
            'accepted',
            'picked_up',
            'in_transit'
        ]);
    }

    public function isCompleted()
    {
        return $this->status === 'delivered';
    }
}