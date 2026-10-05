<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function listings()
    {
        return $this->hasMany(MachineryListing::class);
    }
    
    public function models()
{
    return $this->hasMany(MachineryModel::class);
}
}