<?php

namespace App\Services\Weather;

use App\Models\WeatherStation;

class LocationWeatherService
{
    protected DiseaseRiskService $risk;

    public function __construct(
        DiseaseRiskService $risk
    ) {
        $this->risk = $risk;
    }

    /**
     * Find nearest weather station.
     */
    public function nearestStation(
        float $latitude,
        float $longitude
    ): ?WeatherStation {

        return WeatherStation::query()
            ->selectRaw("
                *,
                (
                    6371 *
                    acos(
                        cos(radians(?))
                        *
                        cos(radians(latitude))
                        *
                        cos(
                            radians(longitude)
                            -
                            radians(?)
                        )
                        +
                        sin(radians(?))
                        *
                        sin(radians(latitude))
                    )
                ) AS distance
            ", [
                $latitude,
                $longitude,
                $latitude,
            ])
            ->where('active', true)
            ->orderBy('distance')
            ->first();
    }

    /**
     * Current weather by GPS.
     */
    public function weather(
        float $latitude,
        float $longitude
    ) {
        $station = $this->nearestStation(
            $latitude,
            $longitude
        );

        if (!$station) {
            return null;
        }

        $data = [
            'station' => $station,
            'observation' => $station
                ->observations()
                ->latest('recorded_at')
                ->first(),
            'alerts' => $station
                ->alerts()
                ->where('active', true)
                ->get(),
        ];

        $data['disease_risk'] = $this->risk->evaluate(
            $data
        );

        return $data;
    }
}