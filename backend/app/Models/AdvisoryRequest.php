<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvisoryRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'advisory_requests';

    /**
     * Category Constants
     */
    public const CATEGORY_CROP_DISEASE = 'Crop Disease';
    public const CATEGORY_LIVESTOCK_DISEASE = 'Livestock Disease';
    public const CATEGORY_NUTRITION = 'Nutrition';
    public const CATEGORY_FERTILIZER = 'Fertilizer';
    public const CATEGORY_PEST_CONTROL = 'Pest Control';
    public const CATEGORY_MECHANIZATION = 'Mechanization';
    public const CATEGORY_SOIL_HEALTH = 'Soil Health';
    public const CATEGORY_IRRIGATION = 'Irrigation';
    public const CATEGORY_MARKETING = 'Marketing';
    public const CATEGORY_POST_HARVEST = 'Post Harvest';
    public const CATEGORY_CLIMATE = 'Climate';
    public const CATEGORY_FINANCE = 'Finance';

    /**
     * Priority Constants
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Status Constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farmer_id',
        'extension_officer_id',
        'category',
        'subject',
        'description',
        'priority',
        'status',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the farmer (User) who created the advisory request.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    /**
     * Get the extension officer assigned to handle the request (if any).
     */
    public function extensionOfficer(): BelongsTo
    {
        return $this->belongsTo(ExtensionOfficer::class, 'extension_officer_id');
    }
}
