<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtensionOfficer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'extension_officers';

    /**
     * Profession Constants
     */
    public const PROFESSION_AGRICULTURAL_EXTENSION_OFFICER = 'Agricultural Extension Officer';
    public const PROFESSION_AGRONOMIST = 'Agronomist';
    public const PROFESSION_VETERINARIAN = 'Veterinarian';
    public const PROFESSION_SOIL_SCIENTIST = 'Soil Scientist';
    public const PROFESSION_IRRIGATION_ENGINEER = 'Irrigation Engineer';
    public const PROFESSION_CROP_PROTECTION_OFFICER = 'Crop Protection Officer';
    public const PROFESSION_LIVESTOCK_OFFICER = 'Livestock Officer';
    public const PROFESSION_FISHERIES_OFFICER = 'Fisheries Officer';
    public const PROFESSION_BEEKEEPING_OFFICER = 'Beekeeping Officer';
    public const PROFESSION_MECHANIZATION_SPECIALIST = 'Mechanization Specialist';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'research_institution_id',
        'profession',
        'specialization',
        'registration_number',
        'phone',
        'region',
        'district',
        'latitude',
        'longitude',
        'consultation_fee',
        'available',
        'verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'consultation_fee' => 'decimal:2',
        'available' => 'boolean',
        'verified' => 'boolean',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the user account associated with the extension officer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the research institution the extension officer is affiliated with (if any).
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(ResearchInstitution::class, 'research_institution_id');
    }
}
