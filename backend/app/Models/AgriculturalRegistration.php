<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AgriculturalRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number', 'registration_type', 'user_id', 'farm_id',
        'business_name', 'region', 'district', 'ward', 'details',
        'status', 'issued_at', 'expires_at', 'approved_by', 'notes',
    ];

    protected $casts = [
        'details' => 'array',
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->registration_number)) {
                $prefix = match ($item->registration_type ?? 'farm') {
                    'trader' => 'TRD',
                    'input_dealer' => 'INP',
                    'processor' => 'PRC',
                    'exporter' => 'EXP',
                    'cooperative' => 'COP',
                    default => 'FRM',
                };
                $item->registration_number = $prefix . '-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
