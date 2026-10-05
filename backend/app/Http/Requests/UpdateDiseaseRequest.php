<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Disease;

class UpdateDiseaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'category' => ['sometimes', 'required', 'string', Rule::in(Disease::CATEGORIES)],
            'target' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'symptoms' => 'sometimes|required|string',
            'causes' => 'nullable|string',
            'prevention' => 'nullable|string',
            'treatment' => 'nullable|string',
            'recommended_products' => 'nullable|string',
            'severity' => ['nullable', 'string', Rule::in(Disease::SEVERITIES)],
            'reportable' => 'nullable|boolean',
        ];
    }
}
