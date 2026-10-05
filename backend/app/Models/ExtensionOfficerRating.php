<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtensionOfficerRating extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'extension_officer_ratings';

    /**
     * Minimum and Maximum Rating Constants
     */
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'extension_officer_id',
        'user_id',
        'rating',
        'review',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the extension officer being rated.
     */
    public function extensionOfficer(): BelongsTo
    {
        return $this->belongsTo(ExtensionOfficer::class);
    }

    /**
     * Get the user (farmer) who submitted the rating/review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
