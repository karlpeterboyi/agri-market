<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DiseaseReport;

class StoreDiseaseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'disease_id' => 'nullable|exists:diseases,id',
            'extension_officer_id' => 'nullable|exists:extension_officers,id',
            'commodity_type' => 'required|string|max:255',
            'commodity_name' => 'required|string|max:255',
            'symptoms' => 'required|string',
            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => ['nullable', 'string', Rule::in(DiseaseReport::STATUSES)],
            'diagnosis_source' => ['nullable', 'string', Rule::in(DiseaseReport::DIAGNOSIS_SOURCES)],
        ];
    }
}
