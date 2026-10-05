<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestockListing extends Model
{
    protected $fillable = [

        'seller_id',

        'livestock_category_id',

        'livestock_breed_id',

        'title',

        'description',

        'sex',

        'age_months',

        'weight',

        'price',

        'quantity',

        'health_status',

        'vaccinated',

        'vaccination_details',

        'region',

        'district',

        'latitude',

        'longitude',

        'status'

    ];

    public function seller()
    {
        return $this->belongsTo(User::class,'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(
            LivestockCategory::class,
            'livestock_category_id'
        );
    }

    public function breed()
    {
        return $this->belongsTo(
            LivestockBreed::class,
            'livestock_breed_id'
        );
    }

    public function images()
    {
        return $this->hasMany(
            LivestockImage::class
        );
    }
}