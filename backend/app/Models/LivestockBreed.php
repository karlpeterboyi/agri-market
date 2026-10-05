<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestockBreed extends Model
{
    protected $fillable = [

        'livestock_category_id',

        'name',

        'origin',

        'description'

    ];

    public function category()
    {
        return $this->belongsTo(
            LivestockCategory::class,
            'livestock_category_id'
        );
    }

    public function listings()
    {
        return $this->hasMany(LivestockListing::class);
    }
}