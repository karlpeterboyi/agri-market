<?php

namespace App\Listeners;

use App\Events\WeatherAlertReceived;
use App\Services\AI\RecommendationEngine;

class GenerateWorkflowFromWeather
{
    public function handle(WeatherAlertReceived $event)
    {
        app(RecommendationEngine::class)
            ->analyse($event->farm);
    }
}