<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'level',
        'language',
        'duration_minutes',
        'thumbnail_path',
        'video_url',
        'content',
        'learning_outcomes',
        'modules',
        'is_free',
        'price',
        'is_published',
        'featured',
        'ends_at',
        'starts_at',
        'max_enrollments',
        'certificate_enabled',
        'format',
        'created_by',
        'research_institution_id',
        'enrollments_count',
    ];

    protected $casts = [
        'learning_outcomes' => 'array',
        'modules' => 'array',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
        'featured' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title) . '-' . Str::random(4);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function institution()
    {
        return $this->belongsTo(ResearchInstitution::class, 'research_institution_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
