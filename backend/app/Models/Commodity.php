<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = [
        'commodity_category_id',
        'name',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(CommodityCategory::class, 'commodity_category_id');
    }

    public function listings()
    {
        return $this->hasMany(ProductListing::class);
    }
}