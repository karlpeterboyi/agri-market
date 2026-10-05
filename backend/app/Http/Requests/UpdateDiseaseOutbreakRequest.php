<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DiseaseOutbreak;

class UpdateDiseaseOutbreakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_id' => 'sometimes|required|exists:diseases,id',
            'region' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|required|string|max:255',
            'reported_cases' => 'nullable|integer|min:0',
            'risk_level' => ['nullable', 'string', Rule::in(DiseaseOutbreak::RISK_LEVELS)],
            'reported_on' => 'nullable|date',
            'active' => 'nullable|boolean',
        ];
    }
}
