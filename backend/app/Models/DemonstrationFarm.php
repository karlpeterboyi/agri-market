<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemonstrationFarm extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'demonstration_farms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'research_institution_id',
        'name',
        'region',
        'district',
        'latitude',
        'longitude',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the research institution that owns the demonstration farm.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(ResearchInstitution::class, 'research_institution_id');
    }

    /**
     * Get the visit bookings for this demonstration farm.
     */
    public function visitBookings(): HasMany
    {
        return $this->hasMany(FarmVisitBooking::class);
    }

    /**
     * Get the field days hosted at this demonstration farm.
     */
    public function fieldDays(): HasMany
    {
        return $this->hasMany(FieldDay::class);
    }

    /**
     * Get the active research demonstrations at this farm.
     */
    public function demonstrations(): HasMany
    {
        return $this->hasMany(FarmDemonstration::class);
    }

    /**
     * Get the downloadable trial results for this farm.
     */
    public function trialResults(): HasMany
    {
        return $this->hasMany(TrialResult::class);
    }
}
