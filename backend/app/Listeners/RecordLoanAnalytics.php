<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class RecordLoanAnalytics
{
    public function handle(object $event): void
    {
        Log::info('Loan workflow event', [

            'event' => class_basename($event),

            'loan_id' => $event->loan->id,

            'status' => $event->loan->status,

        ]);
    }
}