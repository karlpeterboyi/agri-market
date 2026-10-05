<?php

namespace App\Services\Weather;

use App\Models\Farm;
use App\Models\WeatherAlert;
use App\Models\WeatherObservation;
use App\Models\WeatherStation;

class WeatherService
{

    public function latestObservation(
        WeatherStation $station
    )
    {
        return $station
            ->observations()
            ->latest('recorded_at')
            ->first();
    }


    public function activeAlerts(
        string $region
    )
    {
        return WeatherAlert::where(
            'region',
            $region
        )
        ->where(
            'active',
            true
        )
        ->get();
    }


    public function currentWeather(
        WeatherStation $station
    )
    {
        return [

            'station'=>$station,

            'observation'=>
                $this->latestObservation($station),

            'alerts'=>
                $this->activeAlerts(
                    $station->region
                ),

        ];
    }


    /**
     * New Phase 6 integration
     */
    public function farmWeather(Farm $farm)
{
    if (!$farm->weatherStation) {
        return null;
    }

    return $this->currentWeather(
        $farm->weatherStation
    );
}

}