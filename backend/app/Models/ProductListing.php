<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductListing extends Model
{
    protected $appends = ['featured_image_url'];

    protected $fillable = [
        'seller_id',
        'commodity_id',
        'quantity',
        'unit',
        'grade',
        'price',
        'region',
        'district',
        'description',
        'featured_image',
        'image_2',
        'image_3',
        'status',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'listing_id');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $path = $this->featured_image;
        if (!$path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
