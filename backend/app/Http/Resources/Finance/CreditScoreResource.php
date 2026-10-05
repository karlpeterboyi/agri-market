<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'overall_score' => $this->overall_score,

            'rating' => $this->rating,

            'risk_level' => $this->risk_level,

            'last_calculated' => $this->updated_at,

        ];
    }
}