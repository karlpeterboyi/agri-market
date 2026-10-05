<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherAlert extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'weather_station_id',
        'alert_type',
        'severity',
        'title',
        'message',
        'region',
        'district',
        'starts_at',
        'ends_at',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                  Constants                                 */
    /* -------------------------------------------------------------------------- */

    // Alert Types
    public const TYPE_HEAVY_RAIN = 'Heavy Rain';
    public const TYPE_FLOOD = 'Flood';
    public const TYPE_STRONG_WIND = 'Strong Wind';
    public const TYPE_DROUGHT = 'Drought';
    public const TYPE_HEATWAVE = 'Heatwave';
    public const TYPE_COLD_STRESS = 'Cold Stress';
    public const TYPE_FROST = 'Frost';
    public const TYPE_LIGHTNING = 'Lightning';

    public const ALERT_TYPES = [
        self::TYPE_HEAVY_RAIN,
        self::TYPE_FLOOD,
        self::TYPE_STRONG_WIND,
        self::TYPE_DROUGHT,
        self::TYPE_HEATWAVE,
        self::TYPE_COLD_STRESS,
        self::TYPE_FROST,
        self::TYPE_LIGHTNING,
    ];

    // Severities
    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_EXTREME = 'extreme';

    public const SEVERITIES = [
        self::SEVERITY_LOW,
        self::SEVERITY_MODERATE,
        self::SEVERITY_HIGH,
        self::SEVERITY_EXTREME,
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the weather station associated with this alert.
     */
    public function weatherStation(): BelongsTo
    {
        return $this->belongsTo(WeatherStation::class);
    }

    /* -------------------------------------------------------------------------- */
    /*                                Query Scopes                                */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to return only active alerts.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to return currently effective alerts (active and within time frame).
     */
    public function scopeCurrentlyEffective($query)
    {
        $now = now();

        return $query->where('active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    /**
     * Scope a query to filter by alert type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope a query to filter by severity.
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope a query to filter by region and optional district.
     */
    public function scopeByLocation($query, string $region, ?string $district = null)
    {
        $query->where('region', $region);

        if ($district) {
            $query->where(function ($q) use ($district) {
                $q->where('district', $district)
                  ->orWhereNull('district');
            });
        }

        return $query;
    }
}
