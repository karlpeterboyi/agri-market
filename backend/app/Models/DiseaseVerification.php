<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'disease_diagnosis',
        'extension_officer_id',
        'confirmed',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confirmed' => 'boolean',
    ];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the disease diagnosis that was verified.
     */
    public function diseaseDiagnosis(): BelongsTo
    {
        return $this->belongsTo(DiseaseDiagnosis::class, 'disease_diagnosis');
    }

    /**
     * Get the extension officer who performed this verification.
     */
    public function extensionOfficer(): BelongsTo
    {
        return $this->belongsTo(ExtensionOfficer::class, 'extension_officer_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                                Query Scopes                                */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to return only confirmed verifications.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('confirmed', true);
    }

    /**
     * Scope a query to return only rejected/unconfirmed verifications.
     */
    public function scopeRejected($query)
    {
        return $query->where('confirmed', false);
    }

    /**
     * Scope a query to filter verifications by a specific extension officer.
     */
    public function scopeByOfficer($query, int $officerId)
    {
        return $query->where('extension_officer_id', $officerId);
    }
}
