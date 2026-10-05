<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'interest_rate' => $this->interest_rate,

            'minimum_amount' => $this->minimum_amount,

            'maximum_amount' => $this->maximum_amount,

            'maximum_period' => $this->maximum_period_months,

        ];
    }
}