<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmerProfile extends Model
{
    protected $fillable = [
    'user_id',
    'farm_name',
    'region',
    'district',
    'gps_lat',
    'gps_lng',
    'verified',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}