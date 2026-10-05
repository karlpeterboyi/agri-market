<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherStation extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',

        'provider',

        'station_code',

        'country',

        'region',

        'district',

        'latitude',

        'longitude',

        'elevation',

        'active',

    ];

    protected $casts = [

        'latitude'=>'decimal:7',

        'longitude'=>'decimal:7',

        'elevation'=>'decimal:2',

        'active'=>'boolean',

    ];

    public function observations()
    {
        return $this->hasMany(
            WeatherObservation::class
        );
    }

    public function alerts()
    {
        return $this->hasMany(
            WeatherAlert::class
        );
    }
    
    public function farms()
{
    return $this->hasMany(Farm::class);
}
}