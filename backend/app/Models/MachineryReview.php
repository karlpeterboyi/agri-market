<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MachineryReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'machinery_listing_id',
        'user_id',
        'rating',
        'review'
    ];

    public function listing()
    {
        return $this->belongsTo(MachineryListing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}