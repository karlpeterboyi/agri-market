<?php

namespace App\Services\AI;

use App\Models\Farm;
use App\Models\AIRecommendation;

class RecommendationEngine
{
    public function analyse(Farm $farm)
    {
        return AIRecommendation::create([

            'farm_id' => $farm->id,

            'category' => 'crop',

            'priority' => 'high',

            'title' => 'Apply Fertilizer',

            'recommendation' =>
                'Rain is forecast within 24 hours. Apply fertilizer today for improved nutrient uptake.',

            'confidence' => 91.5,

            'inputs' => [

                'weather' => 'forecast',

                'crop_stage' => 'vegetative',

            ],

        ]);
    }
}