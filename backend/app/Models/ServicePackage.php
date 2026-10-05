<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable=[

        'service_id',

        'name',

        'description',

        'price',

        'unit',

        'duration',

        'features',

        'featured',

        'active'

    ];

    protected $casts=[

        'features'=>'array',

        'featured'=>'boolean',

        'active'=>'boolean'

    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}