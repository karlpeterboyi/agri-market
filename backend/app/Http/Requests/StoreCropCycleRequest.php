<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Preferred: cycle belongs to a field block (architecture)
            'field_block_id' => 'required|exists:field_blocks,id',
            // farm_id optional — derived from block if omitted
            'farm_id' => 'nullable|exists:farms,id',
            'crop_id' => 'required|exists:crops,id',
            'crop_variety_id' => 'nullable|exists:crop_varieties,id',
            'season' => 'required|string|max:100',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'nullable|date|after_or_equal:planting_date',
            'actual_harvest_date' => 'nullable|date',
            'area_hectares' => 'required|numeric|min:0.01',
            'expected_yield' => 'nullable|numeric|min:0',
            'actual_yield' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:planned,planted,growing,flowering,harvesting,completed,failed,abandoned',
            'metadata' => 'nullable|array',
            'notes' => 'nullable|string',
        ];
    }
}
