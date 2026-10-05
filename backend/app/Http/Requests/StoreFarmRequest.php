<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',

            'description' => 'nullable|string',

            'farm_type' => 'required|in:crop,livestock,mixed,poultry,aquaculture,horticulture,research,demonstration,plantation',

            'ownership_type' => 'required|in:individual,family,company,cooperative,government,institution,ngo',

            'country' => 'required|string|max:255',

            'region' => 'required|string|max:255',

            'district' => 'required|string|max:255',

            'ward' => 'nullable|string|max:255',

            'village' => 'nullable|string|max:255',

            'address' => 'nullable|string',

            'latitude' => 'nullable|numeric',

            'longitude' => 'nullable|numeric',

            'elevation' => 'nullable|numeric',

            'total_area_hectares' => 'required|numeric|min:0',

            'cultivated_area_hectares' => 'nullable|numeric|min:0',

            'irrigated_area_hectares' => 'nullable|numeric|min:0',

            'registration_number' => 'nullable|string|max:255',

            'certification' => 'nullable|string|max:255',

            'metadata' => 'nullable|array',

        ];
    }
}