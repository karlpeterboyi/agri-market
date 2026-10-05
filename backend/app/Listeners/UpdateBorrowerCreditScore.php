<?php

namespace App\Listeners;

use App\Services\Credit\CreditScoreService;

class UpdateBorrowerCreditScore
{
    public function handle(object $event): void
    {
        app(CreditScoreService::class)

            ->calculate(

                $event->loan->applicant

            );
    }
}