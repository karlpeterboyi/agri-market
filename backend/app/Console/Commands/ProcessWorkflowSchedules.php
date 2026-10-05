<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Workflow\WorkflowScheduler;

class ProcessWorkflowSchedules extends Command
{
    protected $signature = 'workflow:process';

    protected $description = 'Process scheduled workflows';

    public function handle(WorkflowScheduler $scheduler)
    {
        $count = $scheduler->dueSchedules()->count();

        $this->info("Found {$count} scheduled workflows.");

        // Automation logic added in next phase.

        return self::SUCCESS;
    }
}