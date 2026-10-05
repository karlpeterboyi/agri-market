<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Models\FarmWarehouse;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with(['warehouse.farm'])
            ->whereHas('warehouse.farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_warehouse_id')) {
            $query->where('farm_warehouse_id', $request->farm_warehouse_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('low_stock') && $request->boolean('low_stock')) {
            $query->whereColumn('quantity', '<=', 'minimum_quantity');
        }

        return response()->json(
            $query->orderBy('name')->paginate(30)
        );
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $data = $request->validated();

        $warehouse = FarmWarehouse::with('farm')
            ->findOrFail($data['farm_warehouse_id']);

        if ($warehouse->farm->owner_id !== auth()->id()) {
            abort(403, 'You do not own this warehouse.');
        }

        $item = InventoryItem::create($data);

        return response()->json($item->load('warehouse'), 201);
    }

    public function show(InventoryItem $inventoryItem)
    {
        $this->authorizeOwnership($inventoryItem);

        return response()->json(
            $inventoryItem->load([
                'warehouse.farm',
                'stockMovements' => fn ($q) => $q->latest()->limit(20),
            ])
        );
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $this->authorizeOwnership($inventoryItem);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'unit' => 'sometimes|required|string|max:50',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'manufactured_at' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        // Quantity should normally be changed via StockMovement, not direct update
        $inventoryItem->update($validated);

        return response()->json([
            'message' => 'Inventory item updated successfully.',
            'data' => $inventoryItem->fresh()->load('warehouse'),
        ]);
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $this->authorizeOwnership($inventoryItem);

        if ($inventoryItem->quantity > 0) {
            return response()->json([
                'message' => 'Cannot delete item with remaining stock. Adjust quantity to zero first via stock movement.',
            ], 422);
        }

        $inventoryItem->delete();

        return response()->json(['message' => 'Inventory item deleted successfully.']);
    }

    protected function authorizeOwnership(InventoryItem $item): void
    {
        if ($item->warehouse->farm->owner_id !== auth()->id()) {
            abort(403, 'You do not own this inventory item.');
        }
    }
}
