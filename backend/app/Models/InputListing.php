<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class InputListing extends Model
{
    protected $fillable = [

        'seller_id',
        'input_category_id',
        'name',
        'description',
        'price',
        'stock',
        'unit',
        'brand',
        'manufacturer',
        'region',
        'district',
        'images',
        'featured',
        'status'

    ];

    protected $casts = [

        'images'=>'array',
        'featured'=>'boolean',

    ];

    public function seller()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(InputCategory::class,'input_category_id');
    }
    
    public function cartItems()
{
    return $this->hasMany(CartItem::class);
}

public function wishlists()
{
    return $this->hasMany(Wishlist::class);
}

}

