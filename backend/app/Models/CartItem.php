<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [

        'shopping_cart_id',

        'input_listing_id',

        'quantity',

        'unit_price',

    ];

    protected $casts = [

        'unit_price' => 'decimal:2',

    ];

    public function cart()
    {
        return $this->belongsTo(ShoppingCart::class);
    }

    public function product()
    {
        return $this->belongsTo(InputListing::class);
    }

    public function subtotal()
    {
        return $this->quantity * $this->unit_price;
    }
}