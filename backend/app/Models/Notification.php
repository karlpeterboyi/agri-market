<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [

        'uuid',

        'organisation_id',

        'user_id',

        'type',

        'title',

        'message',

        'data',

        'channel',

        'read_at',

    ];

    protected $casts = [

        'data' => 'array',

        'read_at' => 'datetime',

    ];

    protected static function booted()
    {
        static::creating(function ($notification) {

            $notification->uuid ??= (string) Str::uuid();

        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function markAsRead(): void
    {
        $this->update([
            'read_at' => now(),
        ]);
    }
}