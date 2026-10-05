<?php

namespace App\Services\GIS;

class DistanceService
{
    public const EARTH_KM = 6371.0;

    public function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lng2 - $lng1);
        $a = sin($Δφ / 2) ** 2 + cos($φ1) * cos($φ2) * sin($Δλ / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_KM * $c, 2);
    }

    public function kmToMiles(float $km): float
    {
        return round($km * 0.621371, 2);
    }

    /**
     * Very light geocode fallback: region centroids for major TZ regions (approx).
     */
    public function regionCentroid(string $region): ?array
    {
        $map = [
            'dar es salaam' => [-6.7924, 39.2083],
            'dar' => [-6.7924, 39.2083],
            'arusha' => [-3.3869, 36.6830],
            'dodoma' => [-6.1630, 35.7516],
            'mbeya' => [-8.9094, 33.4608],
            'mwanza' => [-2.5164, 32.9171],
            'morogoro' => [-6.8278, 37.6591],
            'tanga' => [-5.0689, 39.0988],
            'kilimanjaro' => [-3.3869, 37.3431],
            'iringa' => [-7.7667, 35.7000],
            'kigoma' => [-4.8769, 29.6267],
            'mtwara' => [-10.2667, 40.1833],
            'ruvuma' => [-10.6833, 35.6500],
            'singida' => [-4.8167, 34.7500],
            'tabora' => [-5.0167, 32.8000],
            'shinyanga' => [-3.6500, 33.4333],
            'kagera' => [-1.3333, 31.8000],
            'pwani' => [-6.8000, 38.9000],
            'coast' => [-6.8000, 38.9000],
            'geita' => [-2.8667, 32.1667],
            'simiyu' => [-2.6333, 34.0000],
            'songwe' => [-9.0000, 33.0000],
            'njombe' => [-9.3333, 34.7667],
            'katavi' => [-6.3500, 31.0500],
            'lindi' => [-9.9980, 39.7165],
            'manyara' => [-4.3167, 36.6833],
        ];
        $key = strtolower(trim($region));

        return isset($map[$key]) ? ['lat' => $map[$key][0], 'lng' => $map[$key][1]] : null;
    }

    public function resolvePoint(?float $lat, ?float $lng, ?string $region): ?array
    {
        if ($lat !== null && $lng !== null) {
            return ['lat' => (float) $lat, 'lng' => (float) $lng];
        }
        if ($region) {
            return $this->regionCentroid($region);
        }

        return null;
    }
}
