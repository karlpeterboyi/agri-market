<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SubsidyApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number', 'subsidy_program_id', 'user_id', 'farm_id',
        'requested_units', 'requested_value', 'status', 'purpose',
        'applicant_data', 'review_notes', 'reviewed_by',
        'submitted_at', 'reviewed_at', 'disbursed_at',
    ];

    protected $casts = [
        'requested_value' => 'decimal:2',
        'applicant_data' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->application_number)) {
                $item->application_number = 'SA-' . strtoupper(Str::random(10));
            }
        });
    }

    public function program()
    {
        return $this->belongsTo(SubsidyProgram::class, 'subsidy_program_id');
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
