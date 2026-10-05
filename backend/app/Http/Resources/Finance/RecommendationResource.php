<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'title' => $this['title'],

            'priority' => $this['priority'],

            'impact' => $this['impact'],

        ];
    }
}