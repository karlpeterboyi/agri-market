<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReadinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'overall' => $this['overall'],

            'risk' => $this['risk'],

            'breakdown' => $this['breakdown'],

        ];
    }
}