<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class FarmVisit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'farm_visits';

    /**
     * Status Constants
     */
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RESCHEDULED = 'rescheduled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'advisory_request_id',
        'scheduled_at',
        'visit_fee',
        'status',
        'recommendations',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'visit_fee' => 'decimal:2',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the advisory request that prompted this farm visit.
     */
    public function advisoryRequest(): BelongsTo
    {
        return $this->belongsTo(AdvisoryRequest::class);
    }

    /**
     * Get the farmer through the advisory request.
     */
    public function farmer(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            AdvisoryRequest::class,
            'id',          // Foreign key on advisory_requests table...
            'id',          // Foreign key on users table...
            'advisory_request_id', // Local key on farm_visits table...
            'farmer_id'    // Local key on advisory_requests table...
        );
    }

    /**
     * Get the assigned extension officer through the advisory request.
     */
    public function extensionOfficer(): HasOneThrough
    {
        return $this->hasOneThrough(
            ExtensionOfficer::class,
            AdvisoryRequest::class,
            'id',                  // Foreign key on advisory_requests table...
            'id',                  // Foreign key on extension_officers table...
            'advisory_request_id', // Local key on farm_visits table...
            'extension_officer_id' // Local key on advisory_requests table...
        );
    }
}
