<?php

namespace App\Services\Weather;

class DiseaseRiskService
{
    public function evaluate(array $weather): array
    {
        $risk = [];

        $obs = $weather['observation'];

        if (!$obs) {

            return [];

        }

        /*
        High humidity
        */

        if (

            $obs->humidity >= 85

            &&

            $obs->temperature >= 22

        ) {

            $risk[] = [

                'risk' => 'High',

                'type' => 'Fungal Diseases',

            ];

        }

        /*
        Armyworm
        */

        if (

            $obs->temperature >= 26

        ) {

            $risk[] = [

                'risk' => 'Medium',

                'type' => 'Fall Armyworm',

            ];

        }

        return $risk;
    }
}