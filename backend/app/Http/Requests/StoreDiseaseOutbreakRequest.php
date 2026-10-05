<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\DiseaseOutbreak;

class StoreDiseaseOutbreakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_id' => 'required|exists:diseases,id',
            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'reported_cases' => 'required|integer|min:1',
            'risk_level' => ['nullable', 'string', Rule::in(DiseaseOutbreak::RISK_LEVELS)],
            'reported_on' => 'required|date',
            'active' => 'nullable|boolean',
        ];
    }
}
