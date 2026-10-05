<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',

            'scientific_name' => 'nullable|string|max:255',

            'category' => 'required|in:food,cash,horticulture,forage,industrial,fruit,vegetable,spice',

            'description' => 'nullable|string',

            'image' => 'nullable|string',

            'active' => 'boolean',

        ];
    }
}