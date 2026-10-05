<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityAttachment extends Model
{
    use HasFactory;

    protected $fillable = [

        'farm_activity_id',

        'type',

        'file_path',

        'mime_type',

        'file_size',

    ];

    public function activity()
    {
        return $this->belongsTo(
            FarmActivity::class,
            'farm_activity_id'
        );
    }
}