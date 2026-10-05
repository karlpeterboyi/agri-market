<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Disease;

class StoreDiseaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'category' => ['required', 'string', Rule::in(Disease::CATEGORIES)],
            'target' => 'required|string|max:255',
            'description' => 'required|string',
            'symptoms' => 'required|string',
            'causes' => 'nullable|string',
            'prevention' => 'nullable|string',
            'treatment' => 'nullable|string',
            'recommended_products' => 'nullable|string',
            'severity' => ['nullable', 'string', Rule::in(Disease::SEVERITIES)],
            'reportable' => 'nullable|boolean',
        ];
    }
}
