<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CropVariety extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'crop_id',

        'name',

        'breeder',

        'maturity_days',

        'yield_per_hectare',

        'yield_unit',

        'drought_tolerance',

        'heat_tolerance',

        'disease_resistance',

        'recommended_regions',

        'description',

        'active',

    ];

    protected $casts = [

        'recommended_regions' => 'array',

        'active' => 'boolean',

    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}