<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'organisation_id',
        'name',
        'module',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($workflow) {
            $workflow->uuid ??= (string) Str::uuid();
        });
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)
            ->orderBy('step_order');
    }

    public function instances()
    {
        return $this->hasMany(WorkflowInstance::class);
    }
    
    public function schedules()
{
    return $this->hasMany(WorkflowSchedule::class);
}

}