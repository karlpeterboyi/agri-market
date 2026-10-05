<?php

namespace App\Console\Commands\Finance;

use Illuminate\Console\Command;
use App\Models\LoanRepayment;
use App\Notifications\LoanStatusChangedNotification;

class SendLoanRepaymentReminders extends Command
{
    protected $signature = 'finance:loan-reminders';

    protected $description = 'Send repayment reminders';

    public function handle()
    {
        $repayments = LoanRepayment::whereDate(
            'due_date',
            now()->addDays(3)
        )
        ->where('status', 'pending')
        ->get();

        foreach ($repayments as $repayment) {

            $user = $repayment
                ->application
                ->applicant;

            $user->notify(

                new LoanStatusChangedNotification(

                    $repayment->application,

                    "Loan repayment of {$repayment->total_amount} is due on {$repayment->due_date->format('d M Y')}."

                )

            );
        }

        $this->info(
            "{$repayments->count()} reminders sent."
        );
    }
}