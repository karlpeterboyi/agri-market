<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeatherObservation extends Model
{
    use HasFactory;

    protected $fillable = [

        'weather_station_id',

        'recorded_at',

        'temperature',

        'humidity',

        'rainfall',

        'wind_speed',

        'pressure',

        'condition',

    ];

    protected $casts=[

        'recorded_at'=>'datetime',

        'temperature'=>'decimal:2',

        'humidity'=>'decimal:2',

        'rainfall'=>'decimal:2',

        'wind_speed'=>'decimal:2',

        'pressure'=>'decimal:2',

    ];

    public function station()
    {
        return $this->belongsTo(
            WeatherStation::class,
            'weather_station_id'
        );
    }
}