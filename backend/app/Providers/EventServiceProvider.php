<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\LoanSubmitted::class => [
            \App\Listeners\SendLoanNotification::class,
            \App\Listeners\RecordLoanAnalytics::class,
        ],
        \App\Events\LoanUnderReview::class => [
            \App\Listeners\SendLoanNotification::class,
            \App\Listeners\RecordLoanAnalytics::class,
        ],
        \App\Events\LoanApproved::class => [
            \App\Listeners\SendLoanNotification::class,
            \App\Listeners\UpdateBorrowerCreditScore::class,
            \App\Listeners\RecordLoanAnalytics::class,
        ],
        \App\Events\LoanRejected::class => [
            \App\Listeners\SendLoanNotification::class,
            \App\Listeners\RecordLoanAnalytics::class,
        ],
        \App\Events\LoanDisbursed::class => [
            \App\Listeners\SendLoanNotification::class,
            \App\Listeners\GenerateLoanRepaymentSchedule::class,
            \App\Listeners\UpdateBorrowerCreditScore::class,
            \App\Listeners\RecordLoanAnalytics::class,
        ],
        \App\Events\LoanCompleted::class => [
            \App\Listeners\SendLoanNotification::class,
            \App\Listeners\UpdateBorrowerCreditScore::class,
            \App\Listeners\RecordLoanAnalytics::class,
        ],
        \App\Events\WeatherAlertReceived::class => [
            \App\Listeners\GenerateWorkflowFromWeather::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
