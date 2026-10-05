<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'installment' => $this->installment_number,

            'due_date' => $this->due_date,

            'principal' => $this->principal_amount,

            'interest' => $this->interest_amount,

            'total' => $this->total_amount,

            'balance' => $this->balance,

            'status' => $this->status,

        ];
    }
}