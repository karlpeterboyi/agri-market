<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgroEcologicalZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'country',

        'zone',

        'code',

        'description',

        'rainfall_min',

        'rainfall_max',

        'temperature_min',

        'temperature_max',

        'altitude_min',

        'altitude_max',

        'regions',

        'districts',

        'soil_types',

        'dominant_crops',

        'livestock',

        'climate_risks',

        'koppen_classification',

        'average_humidity',

        'average_wind_speed',

        'active',

    ];

    protected $casts = [

        'regions'=>'array',

        'districts'=>'array',

        'soil_types'=>'array',

        'dominant_crops'=>'array',

        'livestock'=>'array',

        'climate_risks'=>'array',

        'active'=>'boolean',

    ];

    public function soilProfiles()
    {
        return $this->hasMany(
            SoilProfile::class
        );
    }

    public function cropSuitability()
    {
        return $this->hasMany(
            CropSuitability::class
        );
    }
}