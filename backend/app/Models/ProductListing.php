<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductListing extends Model
{
    public function seller()
{
    return $this->belongsTo(User::class,'seller_id');
}

public function commodity()
{
    return $this->belongsTo(Commodity::class);
}

public function offers()
{
    return $this->hasMany(Offer::class,'listing_id');
}
}
