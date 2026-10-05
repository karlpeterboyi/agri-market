<?php

namespace App\Events;

use App\Models\DiseaseReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiseaseReportedOnFinancedFarm
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public DiseaseReport $diseaseReport
    ) {}
}
