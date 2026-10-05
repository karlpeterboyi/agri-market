<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'rating',
        'verified',
        'operating_region',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class);
    }
}