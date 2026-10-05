<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryModel extends Model
{
    use HasFactory;

    protected $fillable = [

        'machinery_brand_id',

        'machinery_category_id',

        'name',

        'slug',

        'horsepower',

        'fuel_type',

        'transmission',

        'description',

        'active'

    ];

    protected $casts = [

        'active' => 'boolean',

    ];

    public function brand()
    {
        return $this->belongsTo(MachineryBrand::class,'machinery_brand_id');
    }

    public function category()
    {
        return $this->belongsTo(MachineryCategory::class,'machinery_category_id');
    }

    public function listings()
    {
        return $this->hasMany(MachineryListing::class);
    }
}