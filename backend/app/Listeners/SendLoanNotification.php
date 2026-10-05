<?php

namespace App\Listeners;

use App\Notifications\LoanStatusChangedNotification;

class SendLoanNotification
{
    public function handle(object $event): void
    {
        $loan = $event->loan;

        if (!$loan->applicant) {
            return;
        }

        $loan->applicant->notify(

            new LoanStatusChangedNotification(

                $loan,

                "Your loan status is now '{$loan->status}'."

            )

        );
    }
}