<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'transporter_id',
        'registration_number',
        'name',
        'vehicle_type',
        'capacity',
        'price_per_km',
        'price_per_mile',
        'base_latitude',
        'base_longitude',
        'base_region',
        'base_district',
        'available',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'price_per_mile' => 'decimal:2',
        'available' => 'boolean',
        'base_latitude' => 'float',
        'base_longitude' => 'float',
    ];

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class);
    }
}
