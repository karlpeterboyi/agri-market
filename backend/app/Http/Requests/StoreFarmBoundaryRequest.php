<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmBoundaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'farm_id' => [

                'required',

                'exists:farms,id',

            ],

            'boundary' => [

                'required',

                'array',

            ],

            'boundary.coordinates' => [

                'required',

            ],

        ];
    }
}