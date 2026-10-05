<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseDiagnosis extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'disease_report_id',
        'disease_id',
        'confidence',
        'model_name',
        'model_version',
        'predictions',
        'verified_by_expert',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence' => 'float',
        'predictions' => 'array',
        'verified_by_expert' => 'boolean',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the disease report that triggered or contains this diagnosis.
     */
    public function diseaseReport(): BelongsTo
    {
        return $this->belongsTo(DiseaseReport::class);
    }

    /**
     * Get the disease identified by this diagnosis.
     */
    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }
    
    public function verification()
{
    return $this->hasOne(
        DiseaseVerification::class
    );
}

    /* -------------------------------------------------------------------------- */
    /*                                Query Scopes                                */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to return only expert-verified diagnoses.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_by_expert', true);
    }

    /**
     * Scope a query to filter diagnoses above a specific confidence threshold (e.g., 85.00).
     */
    public function scopeHighConfidence($query, float $threshold = 80.0)
    {
        return $query->where('confidence', '>=', $threshold);
    }

    /**
     * Scope a query to filter by AI model name and optional version.
     */
    public function scopeByModel($query, string $modelName, ?string $modelVersion = null)
    {
        $query->where('model_name', $modelName);

        if ($modelVersion) {
            $query->where('model_version', $modelVersion);
        }

        return $query;
    }
}
