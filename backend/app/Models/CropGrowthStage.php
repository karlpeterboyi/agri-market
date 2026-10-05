<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropGrowthStage extends Model
{
    use HasFactory;

    protected $fillable = [

        'crop_id',

        'name',

        'start_day',

        'end_day',

        'description',

        'sort_order',

    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function tasks()
    {
        return $this->hasMany(
            CropStageTask::class
        );
    }

    public function rules()
    {
        return $this->hasMany(
            CropRule::class,
            'crop_growth_stage_id'
        );
    }
}