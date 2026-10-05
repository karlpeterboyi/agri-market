<?php

namespace App\Services\GIS;

/**
 * Lightweight geospatial helpers for farm / field polygons.
 * Coordinates are [lat, lng] pairs in application layer;
 * GeoJSON stores [lng, lat] rings.
 */
class GeometryService
{
    public const EARTH_RADIUS_M = 6371008.8;

    /**
     * Normalize incoming boundary to GeoJSON Polygon.
     * Accepts:
     * - { type, coordinates } GeoJSON
     * - { coordinates: [[[lng,lat],...]] }
     * - { points: [{lat,lng}, ...] } or [[lat,lng],...]
     */
    public function normalizePolygon(array $boundary): array
    {
        if (($boundary['type'] ?? null) === 'Polygon' && !empty($boundary['coordinates'][0])) {
            $ring = $boundary['coordinates'][0];
        } elseif (!empty($boundary['coordinates'][0]) && is_array($boundary['coordinates'][0][0] ?? null)) {
            $ring = $boundary['coordinates'][0];
        } elseif (!empty($boundary['points'])) {
            $ring = [];
            foreach ($boundary['points'] as $p) {
                if (isset($p['lat'], $p['lng'])) {
                    $ring[] = [(float) $p['lng'], (float) $p['lat']];
                } elseif (is_array($p) && count($p) >= 2) {
                    // assume lat,lng
                    $ring[] = [(float) $p[1], (float) $p[0]];
                }
            }
        } else {
            $ring = [];
        }

        $ring = $this->closeRing($ring);

        return [
            'type' => 'Polygon',
            'coordinates' => [$ring],
        ];
    }

    public function closeRing(array $ring): array
    {
        if (count($ring) < 3) {
            return $ring;
        }
        $first = $ring[0];
        $last = $ring[count($ring) - 1];
        if ($first[0] != $last[0] || $first[1] != $last[1]) {
            $ring[] = $first;
        }

        return $ring;
    }

    /** @return list<array{0:float,1:float}> lat,lng pairs */
    public function toLatLngPoints(array $geoJson): array
    {
        $ring = $geoJson['coordinates'][0] ?? [];
        $points = [];
        foreach ($ring as $c) {
            if (count($c) >= 2) {
                $points[] = [(float) $c[1], (float) $c[0]]; // lat, lng
            }
        }
        // drop closing duplicate for display
        if (count($points) > 1) {
            $a = $points[0];
            $b = $points[count($points) - 1];
            if ($a[0] == $b[0] && $a[1] == $b[1]) {
                array_pop($points);
            }
        }

        return $points;
    }

    public function validate(array $pointsLatLng): array
    {
        if (count($pointsLatLng) < 3) {
            return ['valid' => false, 'message' => 'Boundary needs at least 3 points.'];
        }
        foreach ($pointsLatLng as $p) {
            $lat = $p[0];
            $lng = $p[1];
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                return ['valid' => false, 'message' => 'Invalid coordinates in boundary.'];
            }
        }

        return ['valid' => true, 'message' => 'ok'];
    }

    /** Area in m² using spherical excess on lat/lng points */
    public function areaSquareMeters(array $pointsLatLng): float
    {
        $n = count($pointsLatLng);
        if ($n < 3) {
            return 0.0;
        }
        $pts = $pointsLatLng;
        // close
        if ($pts[0][0] != $pts[$n - 1][0] || $pts[0][1] != $pts[$n - 1][1]) {
            $pts[] = $pts[0];
            $n = count($pts);
        }

        $total = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            $lat1 = deg2rad($pts[$i][0]);
            $lng1 = deg2rad($pts[$i][1]);
            $lat2 = deg2rad($pts[$i + 1][0]);
            $lng2 = deg2rad($pts[$i + 1][1]);
            $total += ($lng2 - $lng1) * (2 + sin($lat1) + sin($lat2));
        }

        return abs($total) * self::EARTH_RADIUS_M * self::EARTH_RADIUS_M / 2.0;
    }

    public function hectares(float $areaM2): float
    {
        return round($areaM2 / 10000, 4);
    }

    public function perimeterMeters(array $pointsLatLng): float
    {
        $n = count($pointsLatLng);
        if ($n < 2) {
            return 0.0;
        }
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $a = $pointsLatLng[$i];
            $b = $pointsLatLng[($i + 1) % $n];
            $sum += $this->haversine($a[0], $a[1], $b[0], $b[1]);
        }

        return round($sum, 2);
    }

    public function centroid(array $pointsLatLng): array
    {
        $n = count($pointsLatLng);
        if ($n === 0) {
            return ['lat' => null, 'lng' => null];
        }
        $lat = 0.0;
        $lng = 0.0;
        foreach ($pointsLatLng as $p) {
            $lat += $p[0];
            $lng += $p[1];
        }

        return [
            'lat' => round($lat / $n, 7),
            'lng' => round($lng / $n, 7),
        ];
    }

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lng2 - $lng1);
        $a = sin($Δφ / 2) ** 2 + cos($φ1) * cos($φ2) * sin($Δλ / 2) ** 2;

        return 2 * self::EARTH_RADIUS_M * asin(min(1, sqrt($a)));
    }
}
