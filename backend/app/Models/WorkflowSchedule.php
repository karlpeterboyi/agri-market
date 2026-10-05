<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowSchedule extends Model
{
    use HasFactory;

    protected $fillable = [

        'workflow_id',

        'frequency',

        'start_date',

        'end_date',

        'last_run_at',

        'next_run_at',

        'active',

    ];

    protected $casts = [

        'start_date' => 'date',

        'end_date' => 'date',

        'last_run_at' => 'datetime',

        'next_run_at' => 'datetime',

        'active' => 'boolean',

    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}