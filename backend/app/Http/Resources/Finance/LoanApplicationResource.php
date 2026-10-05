<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->whenLoaded('product') ? $this->product : $this->product()->first();

        return [
            'id' => $this->id,
            'application_number' => $this->application_number ?? $this->reference_number,
            'reference' => $this->application_number ?? $this->reference_number,
            'status' => $this->status,
            'requested_amount' => $this->requested_amount,
            'amount' => $this->requested_amount,
            'approved_amount' => $this->approved_amount ?? null,
            'repayment_period_months' => $this->repayment_period_months,
            'purpose' => $this->purpose,
            'farm_size' => $this->farm_size,
            'farm_size_unit' => $this->farm_size_unit,
            'submitted_at' => optional($this->submitted_at)?->toDateTimeString() ?? $this->submitted_at,
            'approved_at' => optional($this->approved_at)?->toDateTimeString() ?? $this->approved_at,
            'rejected_at' => optional($this->rejected_at)?->toDateTimeString() ?? $this->rejected_at,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'loan_type' => $product->loan_type ?? null,
                'interest_rate' => $product->interest_rate ?? null,
                'financial_institution_id' => $product->financial_institution_id ?? null,
            ] : null,
            'applicant' => $this->whenLoaded('applicant', function () {
                return [
                    'id' => $this->applicant->id,
                    'name' => $this->applicant->name,
                    'email' => $this->applicant->email,
                    'phone' => $this->applicant->phone,
                ];
            }),
            'institution' => optional(optional($product)->financialInstitution ?? optional($product)->institution)->name,
        ];
    }
}
