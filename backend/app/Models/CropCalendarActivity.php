<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropCalendarActivity extends Model
{
    use HasFactory;

    protected $fillable = [

        'crop_calendar_id',

        'activity',

        'scheduled_date',

        'status',

        'recommendation',

    ];

    protected $casts = [

        'scheduled_date'=>'date',

    ];

    public function calendar()
    {
        return $this->belongsTo(
            CropCalendar::class,
            'crop_calendar_id'
        );
    }
}