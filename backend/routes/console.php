<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(
    'finance:loan-reminders'
)->dailyAt('08:00');

Schedule::command(
    'finance:mark-overdue'
)->dailyAt('00:10');

Schedule::command(
    'finance:credit-scores'
)->dailyAt('01:00');

Schedule::command(
    'finance:close-loans'
)->dailyAt('02:00');

Schedule::command(
    'finance:monthly-report'
)->monthlyOn(1, '03:00');

Schedule::command('workflow:process')
    ->everyMinute();