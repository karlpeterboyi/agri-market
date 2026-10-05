<?php

namespace App\Services\Workflow;

use App\Models\WorkflowSchedule;

class WorkflowScheduler
{
    public function dueSchedules()
    {
        return WorkflowSchedule::where('active', true)
            ->where('next_run_at', '<=', now())
            ->get();
    }
}