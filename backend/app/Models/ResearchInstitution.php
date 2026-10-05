<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchInstitution extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'research_institutions';

    /**
     * Institution Types Constants
     */
    public const UNIVERSITY = 'university';
    public const RESEARCH = 'research';
    public const GOVERNMENT = 'government';
    public const NGO = 'ngo';
    public const PRIVATE = 'private';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_user_id',
        'name',
        'acronym',
        'institution_type',
        'description',
        'website',
        'email',
        'phone',
        'region',
        'district',
        'address',
        'verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
    ];

    /* ==========================================
     | Relationships
     | ========================================== */

    /**
     * Get the researchers associated with the institution.
     */
    public function researchers(): HasMany
    {
        return $this->hasMany(Researcher::class);
    }

    /**
     * Get the publications associated with the institution.
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Get the demonstrations associated with the institution.
     */
    public function demonstrations(): HasMany
    {
        return $this->hasMany(Demonstration::class);
    }

    /**
     * Get the projects associated with the institution.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
