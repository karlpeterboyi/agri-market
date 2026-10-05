<?php

namespace App\Services\GIS;

class PolygonValidator
{
    public function validate(array $points): array
    {
        if (count($points) < 4) {

            return [

                'valid' => false,

                'message' =>
                    'Polygon must contain at least four coordinates.',

            ];

        }

        if ($points[0] !== end($points)) {

            return [

                'valid' => false,

                'message' =>
                    'Polygon must be closed.',

            ];

        }

        return [

            'valid' => true,

            'message' => null,

        ];
    }
}