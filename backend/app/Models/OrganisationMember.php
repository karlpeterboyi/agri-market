<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganisationMember extends Model
{
    use HasFactory;

    protected $fillable = [

        'organisation_id',

        'user_id',

        'role',

        'is_owner',

        'active',

        'joined_at',

    ];

    protected $casts = [

        'is_owner' => 'boolean',

        'active' => 'boolean',

        'joined_at' => 'datetime',

    ];

    public function organisation()
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}