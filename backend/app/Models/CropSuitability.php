<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropSuitability extends Model
{
    use HasFactory;

    protected $fillable = [

        'crop_id',

        'agro_ecological_zone_id',

        'rating',

        'score',

        'reason',

        'recommended_varieties',

    ];

    protected $casts = [

        'recommended_varieties'=>'array',

    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function zone()
    {
        return $this->belongsTo(
            AgroEcologicalZone::class,
            'agro_ecological_zone_id'
        );
    }
}