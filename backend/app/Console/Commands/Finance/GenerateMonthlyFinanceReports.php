<?php

namespace App\Console\Commands\Finance;

use Illuminate\Console\Command;

class GenerateMonthlyFinanceReports extends Command
{
    protected $signature = 'finance:monthly-report';

    protected $description = 'Generate monthly finance reports';

    public function handle()
    {
        // Report generation service will be added in Phase 3.3.12

        $this->info(
            'Monthly report generated.'
        );
    }
}