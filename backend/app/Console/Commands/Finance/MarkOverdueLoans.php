<?php

namespace App\Console\Commands\Finance;

use Illuminate\Console\Command;
use App\Models\LoanRepayment;

class MarkOverdueLoans extends Command
{
    protected $signature = 'finance:mark-overdue';

    protected $description = 'Mark overdue repayments';

    public function handle()
    {
        $updated = LoanRepayment::whereDate(
            'due_date',
            '<',
            today()
        )
        ->where('status', 'pending')
        ->update([
            'status' => 'overdue'
        ]);

        $this->info(
            "{$updated} repayments marked overdue."
        );
    }
}