<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FarmerAdvisory extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'crop_calendar_id',

        'advisory_date',

        'title',

        'summary',

        'recommendations',

        'priority',

        'read',

    ];

    protected $casts = [

        'recommendations' => 'array',

        'advisory_date' => 'date',

        'read' => 'boolean',

    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cropCalendar()
    {
        return $this->belongsTo(CropCalendar::class);
    }
}