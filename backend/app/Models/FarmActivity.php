<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'farm_id',

        'field_block_id',

        'crop_cycle_id',

        'user_id',

        'activity_type',

        'title',

        'description',

        'activity_date',

        'quantity',

        'unit',

        'cost',

        'labour_cost',

        'workers',

        'duration_minutes',

        'latitude',

        'longitude',

        'metadata',

    ];

    protected $casts = [

        'activity_date'=>'datetime',

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

    public function cropCycle()
    {
        return $this->belongsTo(CropCycle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(ActivityAttachment::class);
    }
    
    public function stockMovements()
{
    return $this->hasMany(
        StockMovement::class
    );
}
}