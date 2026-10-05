<?php

namespace App\Services\Weather;

use Illuminate\Support\Facades\Http;

class OpenWeatherProvider
{
    public function current(float $latitude, float $longitude)
    {
        // API integration will be implemented in Phase 6.0.2

        return [

            'temperature' => null,

            'humidity' => null,

            'rainfall' => null,

            'wind_speed' => null,

            'pressure' => null,

            'cloud_cover' => null,

            'condition' => null,

            'raw_data' => [],

        ];
    }
}