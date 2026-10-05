<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropRule extends Model
{
    use HasFactory;

    protected $fillable = [

        'crop_id',

        'crop_growth_stage_id',

        'rule_type',

        'conditions',

        'actions',

        'priority',

        'enabled',

    ];

    protected $casts = [

        'conditions'=>'array',

        'actions'=>'array',

        'enabled'=>'boolean',

    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function stage()
    {
        return $this->belongsTo(
            CropGrowthStage::class,
            'crop_growth_stage_id'
        );
    }
}