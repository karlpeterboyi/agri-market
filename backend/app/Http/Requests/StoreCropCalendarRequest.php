<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'crop'=>'required|string|max:255',

            'variety'=>'nullable|string|max:255',

            'region'=>'required|string|max:255',

            'district'=>'required|string|max:255',

            'planting_date'=>'required|date',

            'expected_harvest_date'=>'nullable|date',

            'season'=>'required|string|max:255',

        ];
    }
}