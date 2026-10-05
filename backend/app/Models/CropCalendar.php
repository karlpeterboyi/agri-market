<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropCalendar extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'crop',

        'variety',

        'region',

        'district',

        'planting_date',

        'expected_harvest_date',

        'season',

        'status',

    ];

    protected $casts = [

        'planting_date'=>'date',

        'expected_harvest_date'=>'date',

    ];

    public function farmer()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function activities()
    {
        return $this->hasMany(
            CropCalendarActivity::class
        );
    }
}