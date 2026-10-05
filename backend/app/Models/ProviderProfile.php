<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    protected $fillable = [

        'user_id',

        'business_name',

        'business_registration',

        'tin',

        'vrn',

        'phone',

        'whatsapp',

        'email',

        'website',

        'logo',

        'cover_photo',

        'about',

        'years_experience',

        'service_regions',

        'business_hours',

        'certifications',

        'verified',

        'rating',

        'reviews',

        'completed_jobs'

    ];

    protected $casts = [

        'service_regions'=>'array',

        'business_hours'=>'array',

        'certifications'=>'array',

        'verified'=>'boolean'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}