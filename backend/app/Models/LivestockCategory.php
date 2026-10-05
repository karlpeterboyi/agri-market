<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestockCategory extends Model
{
    protected $fillable = [

        'name',

        'slug',

        'description',

        'icon',

        'active'

    ];

    public function breeds()
    {
        return $this->hasMany(LivestockBreed::class);
    }
}