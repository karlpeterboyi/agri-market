<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'disease_id',
        'extension_officer_id',
        'commodity_type',
        'commodity_name',
        'symptoms',
        'region',
        'district',
        'latitude',
        'longitude',
        'status',
        'diagnosis_source',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Report status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_INVESTIGATING,
        self::STATUS_CONFIRMED,
        self::STATUS_RESOLVED,
        self::STATUS_REJECTED,
    ];

    /**
     * Diagnosis source constants.
     */
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AI = 'ai';
    public const SOURCE_LAB = 'lab';

    public const DIAGNOSIS_SOURCES = [
        self::SOURCE_MANUAL,
        self::SOURCE_AI,
        self::SOURCE_LAB,
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the user (farmer or reporter) who submitted the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the diagnosed disease associated with the report.
     */
    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    /**
     * Get the extension officer assigned to or verifying this report.
     */
    public function extensionOfficer(): BelongsTo
    {
        return $this->belongsTo(ExtensionOfficer::class, 'extension_officer_id');
    }
    
    public function images()
{
    return $this->hasMany(
        DiseaseImage::class
    );
}

public function diagnoses()
{
    return $this->hasMany(
        DiseaseDiagnosis::class
    );
}

    /* -------------------------------------------------------------------------- */
    /*                                Query Scopes                                */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to filter by report status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by region and district.
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
     * Scope a query to filter by commodity type (e.g., Crop or Livestock).
     */
    public function scopeByCommodityType($query, string $commodityType)
    {
        return $query->where('commodity_type', $commodityType);
    }
}
