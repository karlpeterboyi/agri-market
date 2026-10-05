<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestockImage extends Model
{
    protected $fillable = [

        'livestock_listing_id',

        'image',

        'featured'

    ];

    public function listing()
    {
        return $this->belongsTo(
            LivestockListing::class
        );
    }
}