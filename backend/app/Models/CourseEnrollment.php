<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_course_id',
        'user_id',
        'status',
        'progress_percent',
        'enrolled_at',
        'completed_at',
        'certificate_code',
        'payment_status',
        'amount_paid',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markCompleted()
    {
        $this->update([
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
            'certificate_code' => $this->certificate_code ?: 'CERT-' . strtoupper(Str::random(12)),
        ]);
    }
}
