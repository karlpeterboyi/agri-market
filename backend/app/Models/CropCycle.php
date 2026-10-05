<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropCycle extends Model
{
    use HasFactory;

    protected $fillable = [

        'farm_id',

        'field_block_id',

        'crop_id',

        'crop_variety_id',

        'season',

        'planting_date',

        'expected_harvest_date',

        'actual_harvest_date',

        'area_hectares',

        'expected_yield',

        'actual_yield',

        'status',

        'metadata',

    ];

    protected $casts = [

        'planting_date'=>'date',

        'expected_harvest_date'=>'date',

        'actual_harvest_date'=>'date',

        'metadata'=>'array',

    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function fieldBlock()
    {
        return $this->belongsTo(FieldBlock::class);
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function variety()
    {
        return $this->belongsTo(
            CropVariety::class,
            'crop_variety_id'
        );
    }
    
    public function activities()
{
    return $this->hasMany(
        FarmActivity::class
    );
}
}