<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubsidyProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'program_type', 'description', 'implementing_agency',
        'budget_total', 'unit_value', 'unit_label', 'max_units_per_farmer',
        'eligibility_rules', 'required_documents', 'target_regions', 'target_crops',
        'application_start', 'application_end', 'season_start', 'season_end',
        'status', 'is_active', 'created_by',
    ];

    protected $casts = [
        'budget_total' => 'decimal:2',
        'unit_value' => 'decimal:2',
        'eligibility_rules' => 'array',
        'required_documents' => 'array',
        'target_regions' => 'array',
        'target_crops' => 'array',
        'application_start' => 'date',
        'application_end' => 'date',
        'season_start' => 'date',
        'season_end' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->code)) {
                $item->code = 'SUB-' . strtoupper(Str::random(8));
            }
        });
    }

    public function applications()
    {
        return $this->hasMany(SubsidyApplication::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('application_end')->orWhere('application_end', '>=', now());
            });
    }
}
