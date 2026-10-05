<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DiseaseReport;

class UpdateDiseaseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_id' => 'nullable|exists:diseases,id',
            'extension_officer_id' => 'nullable|exists:extension_officers,id',
            'commodity_type' => 'sometimes|required|string|max:255',
            'commodity_name' => 'sometimes|required|string|max:255',
            'symptoms' => 'sometimes|required|string',
            'region' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => ['nullable', 'string', Rule::in(DiseaseReport::STATUSES)],
            'diagnosis_source' => ['nullable', 'string', Rule::in(DiseaseReport::DIAGNOSIS_SOURCES)],
        ];
    }
}
