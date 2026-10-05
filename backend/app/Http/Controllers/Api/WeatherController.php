<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeatherStation;
use App\Services\Weather\WeatherService;
use App\Http\Requests\WeatherLocationRequest;
use App\Services\Weather\LocationWeatherService;

class WeatherController extends Controller
{
    public function current(
        WeatherStation $station,
        WeatherService $service
    )
    {
        return response()->json(

            $service->currentWeather($station)

        );
    }
    
    public function byLocation(

    WeatherLocationRequest $request,

    LocationWeatherService $service

) {

    return response()->json(

        $service->weather(

            $request->latitude,

            $request->longitude

        )

    );

}

}