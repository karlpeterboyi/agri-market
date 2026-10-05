<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkflowInstance extends Model
{
    protected $fillable = [

        'uuid',

        'workflow_id',

        'workflowable_type',

        'workflowable_id',

        'status',

        'current_step',

        'started_at',

        'completed_at',

    ];

    protected $casts = [

        'started_at'=>'datetime',

        'completed_at'=>'datetime',

    ];

    protected static function booted()
    {
        static::creating(function ($instance){

            $instance->uuid ??= (string) Str::uuid();

        });
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function workflowable()
    {
        return $this->morphTo();
    }
}