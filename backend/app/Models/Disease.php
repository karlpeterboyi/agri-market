<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'scientific_name',
        'category',
        'target',
        'description',
        'symptoms',
        'causes',
        'prevention',
        'treatment',
        'recommended_products',
        'severity',
        'reportable',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reportable' => 'boolean',
    ];

    /**
     * Allowed Category constants.
     */
    public const CATEGORIES = [
        'Crop Disease',
        'Livestock Disease',
        'Pest',
        'Parasite',
        'Nutritional Disorder',
    ];

    /**
     * Allowed Severity constants.
     */
    public const SEVERITIES = [
        'low',
        'medium',
        'high',
        'critical',
    ];

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by target (e.g., Maize, Cattle).
     */
    public function scopeByTarget($query, string $target)
    {
        return $query->where('target', $target);
    }

    /**
     * Scope a query to return only reportable diseases.
     */
    public function scopeReportable($query)
    {
        return $query->where('reportable', true);
    }
}
