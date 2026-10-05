<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeatherStation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WeatherStationController extends Controller
{
    /**
     * Display a listing of weather stations.
     */
    public function index(Request $request)
    {
        $query = WeatherStation::query();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('station_code')) {
            $query->where(
                'station_code',
                $request->station_code
            );
        }

        if ($request->filled('provider')) {
            $query->where(
                'provider',
                $request->provider
            );
        }

        if ($request->has('active')) {
            $query->where(
                'active',
                filter_var(
                    $request->active,
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Location Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('region')) {
            $query->where(
                'region',
                $request->region
            );
        }

        if ($request->filled('district')) {
            $query->where(
                'district',
                $request->district
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'station_code',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'name',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'provider',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'region',
                    'ILIKE',
                    "%{$search}%"
                )
                ->orWhere(
                    'district',
                    'ILIKE',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max(
                (int) $request->get('per_page', 15),
                1
            ),
            100
        );

        $stations = $query
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $stations,
        ]);
    }

    /**
     * Store a new weather station.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'station_code' => [
                'required',
                'string',
                'max:255',
                'unique:weather_stations,station_code',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'provider' => [
                'required',
                'string',
                'max:255',
            ],

            'country' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'region' => [
                'required',
                'string',
                'max:255',
            ],

            'district' => [
                'nullable',
                'string',
                'max:255',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'elevation' => [
                'nullable',
                'numeric',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $station = WeatherStation::create(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Weather station created successfully.',
            'data' => $station,
        ], 201);
    }

    /**
     * Display a specific weather station.
     */
    public function show(WeatherStation $station)
    {
        $station->load([
            'observations' => function ($query) {
                $query
                    ->latest('recorded_at')
                    ->limit(20);
            },
            'alerts' => function ($query) {
                $query
                    ->latest('starts_at')
                    ->limit(20);
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $station,
        ]);
    }

    /**
     * Update a weather station.
     */
    public function update(
        Request $request,
        WeatherStation $station
    ) {
        $validated = $request->validate([

            'station_code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(
                    'weather_stations',
                    'station_code'
                )->ignore($station->id),
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'provider' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'country' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'region' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'district' => [
                'nullable',
                'string',
                'max:255',
            ],

            'latitude' => [
                'sometimes',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'sometimes',
                'numeric',
                'between:-180,180',
            ],

            'elevation' => [
                'nullable',
                'numeric',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $station->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Weather station updated successfully.',
            'data' => $station->fresh(),
        ]);
    }

    /**
     * Delete a weather station.
     */
    public function destroy(WeatherStation $station)
    {
        $station->delete();

        return response()->json([
            'success' => true,
            'message' => 'Weather station deleted successfully.',
        ]);
    }

    /**
     * Return the latest observation for a station.
     */
    public function latestObservation(
        WeatherStation $station
    ) {
        $observation = $station
            ->observations()
            ->latest('recorded_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $observation,
        ]);
    }

    /**
     * Return currently effective alerts for a station.
     */
    public function alerts(
        WeatherStation $station
    ) {
        $alerts = $station
            ->alerts()
            ->currentlyEffective()
            ->latest('starts_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }
}