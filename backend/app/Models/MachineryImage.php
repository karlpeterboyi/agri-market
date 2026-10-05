<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'machinery_listing_id',
        'image',
        'is_cover',
        'sort_order'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(MachineryListing::class);
    }
}