<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiseaseVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_diagnosis' => 'required|exists:disease_diagnoses,id',
            'extension_officer_id' => 'required|exists:extension_officers,id',
            'confirmed' => 'required|boolean',
            'notes' => 'nullable|string',
        ];
    }
}
