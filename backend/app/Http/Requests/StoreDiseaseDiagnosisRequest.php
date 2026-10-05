<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiseaseDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_report_id' => 'required|exists:disease_reports,id',
            'disease_id' => 'nullable|exists:diseases,id',
            'confidence' => 'required|numeric|between:0,100',
            'model_name' => 'required|string|max:255',
            'model_version' => 'nullable|string|max:255',
            'predictions' => 'nullable|array',
            'verified_by_expert' => 'boolean',
        ];
    }
}
