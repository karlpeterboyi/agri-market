<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [

        'uuid',

        'organisation_id',

        'user_id',

        'auditable_type',

        'auditable_id',

        'event',

        'action',

        'ip_address',

        'user_agent',

        'old_values',

        'new_values',

        'metadata',

    ];

    protected $casts = [

        'old_values' => 'array',

        'new_values' => 'array',

        'metadata' => 'array',

    ];

    protected static function booted()
    {
        static::creating(function ($log) {

            if (!$log->uuid) {

                $log->uuid = (string) Str::uuid();

            }

        });
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function organisation()
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}