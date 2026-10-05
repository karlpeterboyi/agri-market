<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Researcher extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'researchers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'research_institution_id',
        'specialization',
        'qualification',
        'registration_number',
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
     * Get the user account associated with the researcher.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the institution that the researcher belongs to.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(ResearchInstitution::class, 'research_institution_id');
    }

    /**
     * Get the research projects conducted by the researcher.
     */
    public function researchProjects(): HasMany
    {
        return $this->hasMany(ResearchProject::class);
    }

    /**
     * Get publications through research projects.
     * Hierarchy: Researcher -> Research Projects -> Publications
     */
    public function publications(): HasManyThrough
    {
        return $this->hasManyThrough(Publication::class, ResearchProject::class);
    }
}
