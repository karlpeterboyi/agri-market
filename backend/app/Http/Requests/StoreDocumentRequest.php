<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'file' => 'required|file|max:10240',

            'category' => 'nullable|string',

            'visibility' => 'nullable|in:private,organisation,public',

        ];
    }
}