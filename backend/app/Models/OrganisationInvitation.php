<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class OrganisationInvitation extends Model
{
    use HasFactory;

    protected $fillable = [

        'uuid',

        'organisation_id',

        'invited_by',

        'email',

        'phone',

        'role',

        'token',

        'expires_at',

        'accepted_at',

        'declined_at',

    ];

    protected $casts = [

        'expires_at' => 'datetime',

        'accepted_at' => 'datetime',

        'declined_at' => 'datetime',

    ];

    protected static function booted()
    {
        static::creating(function ($invitation) {

            $invitation->uuid ??= (string) Str::uuid();

            $invitation->token ??= Str::random(64);

        });
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function inviter()
    {
        return $this->belongsTo(
            User::class,
            'invited_by'
        );
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }
}