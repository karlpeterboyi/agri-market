<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FieldBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'name',
        'code',
        'boundary',
        'area_hectares',
        'area_unit',
        'soil_type',
        'irrigation_type',
        'status',
    ];

    protected $casts = [
        'boundary' => 'array',
        'area_hectares' => 'float',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function cropCycles()
    {
        return $this->hasMany(CropCycle::class);
    }

    public function currentCropCycle()
    {
        return $this->hasOne(CropCycle::class)
            ->whereNotIn('status', ['completed', 'failed', 'abandoned'])
            ->latestOfMany();
    }

    public function activities()
    {
        return $this->hasMany(FarmActivity::class);
    }
}
