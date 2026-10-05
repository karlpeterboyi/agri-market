<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FarmBoundary extends Model
{
    use HasFactory;

    protected $fillable = [

        'farm_id',

        'boundary',

        'area_hectares',

        'perimeter_meters',

        'centroid_latitude',

        'centroid_longitude',

    ];

    protected $casts = [

        'boundary' => 'array',

    ];

    public function farm()
    {
        return $this->belongsTo(
            Farm::class
        );
    }
}