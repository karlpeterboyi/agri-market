<?php

namespace App\Events;

use App\Models\Farm;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WeatherAlertReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Farm $farm,
        public array $weather
    ) {}
}