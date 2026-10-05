<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchPublication extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'research_publications';

    /**
     * Category Constants
     */
    public const CATEGORY_SOIL_SCIENCE = 'Soil Science';
    public const CATEGORY_IRRIGATION = 'Irrigation';
    public const CATEGORY_CLIMATE = 'Climate';
    public const CATEGORY_SEEDS = 'Seeds';
    public const CATEGORY_FERTILIZERS = 'Fertilizers';
    public const CATEGORY_LIVESTOCK = 'Livestock';
    public const CATEGORY_FISHERIES = 'Fisheries';
    public const CATEGORY_POULTRY = 'Poultry';
    public const CATEGORY_PEST_MANAGEMENT = 'Pest Management';
    public const CATEGORY_DISEASES = 'Diseases';
    public const CATEGORY_MECHANIZATION = 'Mechanization';
    public const CATEGORY_AI_AGRICULTURE = 'AI Agriculture';
    public const CATEGORY_BIOTECHNOLOGY = 'Biotechnology';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'research_institution_id',
        'researcher_id',
        'title',
        'abstract',
        'content',
        'category',
        'crop',
        'livestock',
        'file',
        'published_on',
        'featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_on' => 'date',
        'featured' => 'boolean',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the institution that owns the publication.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(ResearchInstitution::class, 'research_institution_id');
    }

    /**
     * Get the researcher who authored the publication.
     */
    public function researcher(): BelongsTo
    {
        return $this->belongsTo(Researcher::class);
    }

    /**
     * Get the farmer adoptions following the hierarchy:
     * Research Publication -> Farmer Adoption
     */
    public function farmerAdoptions(): HasMany
    {
        return $this->hasMany(FarmerAdoption::class, 'research_publication_id');
    }
}
