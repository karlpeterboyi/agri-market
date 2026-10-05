<?php

namespace App\Services\GIS;

class GeoJsonService
{
    public function polygon(array $coordinates): array
    {
        return [

            'type' => 'Polygon',

            'coordinates' => [

                $coordinates,

            ],

        ];
    }

    public function coordinates(array $geoJson): array
    {
        return
            $geoJson['coordinates'][0] ?? [];
    }
}