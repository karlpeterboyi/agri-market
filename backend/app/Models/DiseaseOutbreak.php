<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseOutbreak extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'disease_id',
        'region',
        'district',
        'reported_cases',
        'risk_level',
        'reported_on',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reported_cases' => 'integer',
        'reported_on' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Risk level constants.
     */
    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';
    public const RISK_CRITICAL = 'critical';

    public const RISK_LEVELS = [
        self::RISK_LOW,
        self::RISK_MEDIUM,
        self::RISK_HIGH,
        self::RISK_CRITICAL,
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the disease associated with this outbreak.
     */
    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    /* -------------------------------------------------------------------------- */
    /*                                Query Scopes                                */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to return only active outbreaks.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to filter by risk level.
     */
    public function scopeByRiskLevel($query, string $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    /**
     * Scope a query to filter by region and optional district.
     */
    public function scopeByLocation($query, string $region, ?string $district = null)
    {
        $query->where('region', $region);

        if ($district) {
            $query->where('district', $district);
        }

        return $query;
    }

    /**
     * Scope a query to return high or critical risk outbreaks.
     */
    public function scopeSevere($query)
    {
        return $query->whereIn('risk_level', [self::RISK_HIGH, self::RISK_CRITICAL]);
    }
}
