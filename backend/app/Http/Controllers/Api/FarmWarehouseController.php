<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\FarmWarehouse;
use Illuminate\Http\Request;

class FarmWarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = FarmWarehouse::with(['farm', 'inventoryItems'])
            ->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100', // main, cold_storage, input_store, chemical_store
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $farm = Farm::where('id', $validated['farm_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $warehouse = FarmWarehouse::create([
            ...$validated,
            'active' => $validated['active'] ?? true,
            'organisation_id' => $farm->organisation_id,
        ]);

        return response()->json($warehouse->load('farm'), 201);
    }

    public function show(FarmWarehouse $farmWarehouse)
    {
        $this->authorizeOwnership($farmWarehouse);

        return response()->json(
            $farmWarehouse->load([
                'farm',
                'inventoryItems' => fn ($q) => $q->orderBy('name'),
            ])
        );
    }

    public function update(Request $request, FarmWarehouse $farmWarehouse)
    {
        $this->authorizeOwnership($farmWarehouse);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $farmWarehouse->update($validated);

        return response()->json([
            'message' => 'Warehouse updated successfully.',
            'data' => $farmWarehouse->fresh()->load('farm'),
        ]);
    }

    public function destroy(FarmWarehouse $farmWarehouse)
    {
        $this->authorizeOwnership($farmWarehouse);

        if ($farmWarehouse->inventoryItems()->exists()) {
            return response()->json([
                'message' => 'Cannot delete warehouse that still has inventory items.',
            ], 422);
        }

        $farmWarehouse->delete();

        return response()->json(['message' => 'Warehouse deleted successfully.']);
    }

    protected function authorizeOwnership(FarmWarehouse $warehouse): void
    {
        if ($warehouse->farm->owner_id !== auth()->id()) {
            abort(403, 'You do not own this warehouse.');
        }
    }
}
