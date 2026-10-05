<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [

        'farm_id',

        'category',

        'priority',

        'title',

        'recommendation',

        'inputs',

        'confidence',

        'accepted',

        'accepted_at',

    ];

    protected $casts = [

        'inputs' => 'array',

        'accepted' => 'boolean',

        'accepted_at' => 'datetime',

    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}