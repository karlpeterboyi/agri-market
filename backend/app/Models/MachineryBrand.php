<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryBrand extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',

        'slug',

        'country',

        'logo',

        'description',

        'active'

    ];

    protected $casts = [

        'active' => 'boolean'

    ];

    public function models()
    {
        return $this->hasMany(MachineryModel::class);
    }
}