<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoilProfile extends Model
{
    use HasFactory;

    protected $fillable = [

        'agro_ecological_zone_id',

        'soil_type',

        'ph_min',

        'ph_max',

        'organic_matter',

        'drainage',

        'texture',

        'fertility',

        'nutrient_levels',

        'recommended_amendments',

    ];

    protected $casts = [

        'nutrient_levels'=>'array',

        'recommended_amendments'=>'array',

    ];

    public function zone()
    {
        return $this->belongsTo(
            AgroEcologicalZone::class,
            'agro_ecological_zone_id'
        );
    }
}