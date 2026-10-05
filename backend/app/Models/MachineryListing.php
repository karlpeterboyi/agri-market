<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryListing extends Model
{
    use HasFactory;

    protected $fillable = [

        'owner_id',

        'machinery_category_id',

        'machinery_brand_id',

        'machinery_model_id',

        'title',

        'description',

        'manufacture_year',

        'condition',

        'horsepower',

        'engine_hours',

        'fuel_type',

        'transmission',

        'sale_price',

        'rental_price',

        'rental_period',

        'for_sale',

        'for_rent',

        'operator_included',

        'region',

        'district',

        'ward',

        'village',

        'latitude',

        'longitude',

        'cover_photo',

        'gallery',

        'available',

        'verified',

        'featured',

        'status'

    ];

    protected $casts = [

        'gallery' => 'array',

        'for_sale' => 'boolean',

        'for_rent' => 'boolean',

        'operator_included' => 'boolean',

        'available' => 'boolean',

        'verified' => 'boolean',

        'featured' => 'boolean',

        'sale_price' => 'decimal:2',

        'rental_price' => 'decimal:2',

    ];

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id');
    }

    public function category()
    {
        return $this->belongsTo(MachineryCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(MachineryBrand::class);
    }

    public function model()
    {
        return $this->belongsTo(MachineryModel::class,'machinery_model_id');
    }
    
    public function images()
{
    return $this->hasMany(MachineryImage::class);
}

public function reviews()
{
    return $this->hasMany(MachineryReview::class);
}

public function bookings()
{
    return $this->hasMany(MachineryBooking::class);
}
}