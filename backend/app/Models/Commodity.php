<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    public function listings()
{
    return $this->hasMany(ProductListing::class);
}
}
