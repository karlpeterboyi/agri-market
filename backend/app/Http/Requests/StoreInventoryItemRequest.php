<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'farm_warehouse_id' => 'required|exists:farm_warehouses,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100', // seed, fertilizer, pesticide, feed, fuel, tool, other
            'brand' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'manufactured_at' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ];
    }
}
