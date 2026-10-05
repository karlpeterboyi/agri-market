<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = [

        'workflow_id',

        'name',

        'step_order',

        'task_title',

        'task_description',

        'assign_role',

        'due_after_days',

        'approval_required',

    ];

    protected $casts = [

        'approval_required'=>'boolean',

    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}