<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'farm_id' => 'required|exists:farms,id',
            'field_block_id' => 'nullable|exists:field_blocks,id',
            'crop_cycle_id' => 'nullable|exists:crop_cycles,id',
            'activity_type' => 'required|string|max:100', // planting, weeding, fertilizing, spraying, irrigation, harvesting, scouting, other
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date',
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'labour_cost' => 'nullable|numeric|min:0',
            'workers' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'metadata' => 'nullable|array',
        ];
    }
}
