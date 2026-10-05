<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropStageTask extends Model
{
    use HasFactory;

    protected $fillable = [

        'crop_growth_stage_id',

        'task',

        'task_type',

        'days_after_stage',

        'priority',

        'mandatory',

        'instructions',

    ];

    protected $casts = [

        'mandatory'=>'boolean',

    ];

    public function stage()
    {
        return $this->belongsTo(
            CropGrowthStage::class,
            'crop_growth_stage_id'
        );
    }
}