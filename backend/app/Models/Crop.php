<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'name',

        'scientific_name',

        'category',

        'description',

        'image',

        'active',

    ];

    protected $casts = [

        'active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function varieties()
    {
        return $this->hasMany(CropVariety::class);
    }

    public function growthStages()
    {
        return $this->hasMany(CropGrowthStage::class);
    }

    public function rules()
    {
        return $this->hasMany(CropRule::class);
    }
    
    public function suitability()
{
    return $this->hasMany(
        CropSuitability::class
    );
}

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}