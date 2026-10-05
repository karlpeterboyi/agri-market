<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [

        'uuid',

        'organisation_id',

        'farm_id',

        'created_by',

        'title',

        'description',

        'priority',

        'status',

        'start_date',

        'due_date',

        'completed_at',

    ];

    protected $casts = [

        'start_date' => 'date',

        'due_date' => 'date',

        'completed_at' => 'datetime',

    ];

    protected static function booted()
    {
        static::creating(function ($task) {

            $task->uuid ??= (string) Str::uuid();

        });
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }
    
    public function comments()
{
    return $this->hasMany(TaskComment::class);
}

public function checklist()
{
    return $this->hasMany(TaskChecklist::class);
}

public function documents()
{
    return $this->morphMany(
        Document::class,
        'documentable'
    );
}

public function workflowInstance()
{
    return $this->morphOne(
        WorkflowInstance::class,
        'workflowable'
    );
}

public function reminders()
{
    return $this->hasMany(WorkflowReminder::class);
}

}