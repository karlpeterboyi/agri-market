<?php

namespace App\Console\Commands\Finance;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\Credit\CreditScoreService;

class RecalculateCreditScores extends Command
{
    protected $signature = 'finance:credit-scores';

    protected $description = 'Recalculate all borrower credit scores';

    public function handle(
        CreditScoreService $service
    )
    {
        User::chunk(
            100,
            function ($users) use ($service) {

                foreach ($users as $user) {

                    $service->calculate($user);

                }

            }
        );

        $this->info(
            'Credit scores updated.'
        );
    }
}