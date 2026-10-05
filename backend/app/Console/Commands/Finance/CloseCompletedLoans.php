<?php

namespace App\Console\Commands\Finance;

use Illuminate\Console\Command;
use App\Models\LoanApplication;
use App\Services\Finance\LoanWorkflowService;

class CloseCompletedLoans extends Command
{
    protected $signature = 'finance:close-loans';

    protected $description = 'Automatically close fully repaid loans';

    public function handle(
        LoanWorkflowService $workflow
    )
    {
        LoanApplication::where(
            'status',
            'disbursed'
        )

        ->chunk(

            100,

            function ($loans) use ($workflow) {

                foreach ($loans as $loan) {

                    $workflow->complete($loan);

                }

            }

        );

        $this->info(
            'Completed loans checked.'
        );
    }
}