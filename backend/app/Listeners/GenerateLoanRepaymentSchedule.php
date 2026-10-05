<?php

namespace App\Listeners;

use App\Events\LoanDisbursed;
use App\Services\Finance\LoanWorkflowService;

class GenerateLoanRepaymentSchedule
{
    public function handle(
        LoanDisbursed $event
    ): void {

        app(LoanWorkflowService::class)

            ->generateRepaymentSchedule(

                $event->loan

            );
    }
}