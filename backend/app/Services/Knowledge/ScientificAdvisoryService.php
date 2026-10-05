<?php

namespace App\Services\Knowledge;

use App\Models\CropCalendar;
use App\Services\Weather\LocationWeatherService;

class ScientificAdvisoryService
{
    public function __construct(
        protected CropRuleEngine $ruleEngine,
        protected LocationWeatherService $weatherService,
    ) {
    }

    public function generate(
        CropCalendar $calendar,
        float $latitude,
        float $longitude
    ): array {

        $weather = $this->weatherService
            ->weather($latitude, $longitude);

        $context = [

            'crop_id' => $calendar->crop_id,

            'humidity' => optional(
                $weather['observation'] ?? null
            )->humidity,

            'temperature' => optional(
                $weather['observation'] ?? null
            )->temperature,

            'rainfall' => optional(
                $weather['observation'] ?? null
            )->rainfall,

        ];

        return [

            'weather' => $weather,

            'recommendations' =>

                $this->ruleEngine
                    ->evaluate($context),

        ];
    }
}