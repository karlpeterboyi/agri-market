<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommodityCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'active'
    ];

    public function commodities()
    {
        return $this->hasMany(Commodity::class);
    }
}