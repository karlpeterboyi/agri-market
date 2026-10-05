<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    /**
     * Display stock movements.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with([
            'inventoryItem.warehouse',
            'farmActivity',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('inventory_item_id')) {
            $query->where(
                'inventory_item_id',
                $request->inventory_item_id
            );
        }

        if ($request->filled('farm_activity_id')) {
            $query->where(
                'farm_activity_id',
                $request->farm_activity_id
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        $movements = $query
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }

    /**
     * Display a single stock movement.
     */
    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load([
            'inventoryItem.warehouse',
            'farmActivity',
        ]);

        return response()->json([
            'success' => true,
            'data' => $stockMovement,
        ]);
    }

    /**
     * Record a stock movement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],

            'farm_activity_id' => [
                'nullable',
                'integer',
                'exists:farm_activities,id',
            ],

            'type' => [
                'required',
                Rule::in([
                    'purchase',
                    'usage',
                    'adjustment',
                    'transfer',
                    'sale',
                    'return',
                    'loss',
                ]),
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Transfer protection
        |--------------------------------------------------------------------------
        |
        | The current stock_movements schema only contains one
        | inventory_item_id. Therefore there is no destination item or
        | destination warehouse field available to complete a real
        | two-sided transfer.
        |
        */

        if ($validated['type'] === 'transfer') {
            return response()->json([
                'success' => false,
                'message' =>
                    'Transfer movements cannot be completed through this endpoint because the current stock movement schema does not define a destination inventory item or warehouse.',
            ], 422);
        }

        $movement = DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Lock inventory item
            |--------------------------------------------------------------------------
            |
            | Prevent two simultaneous stock movements from calculating
            | the balance from the same old quantity.
            |
            */

            $item = InventoryItem::whereKey(
                $validated['inventory_item_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            $currentQuantity = (float) $item->quantity;
            $movementQuantity = (float) $validated['quantity'];

            /*
            |--------------------------------------------------------------------------
            | Calculate new balance
            |--------------------------------------------------------------------------
            */

            switch ($validated['type']) {

                case 'purchase':
                case 'return':

                    $newBalance =
                        $currentQuantity + $movementQuantity;

                    break;

                case 'usage':
                case 'sale':
                case 'loss':

                    $newBalance =
                        $currentQuantity - $movementQuantity;

                    break;

                case 'adjustment':

                    /*
                    |--------------------------------------------------------------------------
                    | Adjustment
                    |--------------------------------------------------------------------------
                    |
                    | An adjustment quantity is treated as a positive correction
                    | to the existing balance.
                    |
                    | A negative correction can be represented by supplying
                    | the adjustment through a decrease-type movement where
                    | appropriate.
                    |
                    */

                    $newBalance =
                        $currentQuantity + $movementQuantity;

                    break;

                default:

                    throw new \RuntimeException(
                        'Unsupported stock movement type.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent negative stock
            |--------------------------------------------------------------------------
            */

            if ($newBalance < 0) {
                throw new \RuntimeException(
                    'Insufficient stock. The movement would result in a negative inventory balance.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update inventory quantity
            |--------------------------------------------------------------------------
            */

            $item->quantity = $newBalance;
            $item->save();

            /*
            |--------------------------------------------------------------------------
            | Create movement record
            |--------------------------------------------------------------------------
            */

            return StockMovement::create([
                'inventory_item_id' =>
                    $item->id,

                'farm_activity_id' =>
                    $validated['farm_activity_id'] ?? null,

                'type' =>
                    $validated['type'],

                'quantity' =>
                    $movementQuantity,

                'balance_after' =>
                    $newBalance,

                'remarks' =>
                    $validated['remarks'] ?? null,
            ]);
        });

        $movement->load([
            'inventoryItem.warehouse',
            'farmActivity',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock movement recorded successfully.',
            'data' => $movement,
        ], 201);
    }

    /**
     * Update a stock movement.
     *
     * Stock movements are historical records, therefore modifying an
     * existing movement is intentionally restricted.
     */
    public function update(
        Request $request,
        StockMovement $stockMovement
    ) {
        return response()->json([
            'success' => false,
            'message' =>
                'Stock movements are immutable historical records. Create a new adjustment movement instead of modifying an existing movement.',
        ], 422);
    }

    /**
     * Delete a stock movement.
     *
     * Historical stock movements should not be deleted because doing so
     * would break the inventory audit trail.
     */
    public function destroy(StockMovement $stockMovement)
    {
        return response()->json([
            'success' => false,
            'message' =>
                'Stock movements cannot be deleted because they form part of the inventory audit trail.',
        ], 422);
    }

    /**
     * Get the current stock balance for an inventory item.
     */
    public function balance(InventoryItem $inventoryItem)
    {
        $inventoryItem->load('warehouse');

        return response()->json([
            'success' => true,
            'data' => [
                'inventory_item_id' =>
                    $inventoryItem->id,

                'name' =>
                    $inventoryItem->name,

                'sku' =>
                    $inventoryItem->sku,

                'unit' =>
                    $inventoryItem->unit,

                'quantity' =>
                    $inventoryItem->quantity,

                'minimum_quantity' =>
                    $inventoryItem->minimum_quantity,

                'unit_cost' =>
                    $inventoryItem->unit_cost,

                'stock_value' =>
                    round(
                        (float) $inventoryItem->quantity *
                        (float) $inventoryItem->unit_cost,
                        2
                    ),

                'low_stock' =>
                    (float) $inventoryItem->quantity <=
                    (float) $inventoryItem->minimum_quantity,

                'warehouse' =>
                    $inventoryItem->warehouse,
            ],
        ]);
    }

    /**
     * Get movement history for an inventory item.
     */
    public function history(
        Request $request,
        InventoryItem $inventoryItem
    ) {
        $query = $inventoryItem
            ->stockMovements()
            ->with('farmActivity')
            ->latest();

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        if ($request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        $movements = $query
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }

    /**
     * Get low-stock inventory items.
     */
    public function lowStock()
    {
        $items = InventoryItem::with('warehouse')
            ->whereColumn(
                'quantity',
                '<=',
                'minimum_quantity'
            )
            ->orderBy('quantity')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Get stock movement summary.
     */
    public function summary(Request $request)
    {
        $query = StockMovement::query();

        if ($request->filled('inventory_item_id')) {
            $query->where(
                'inventory_item_id',
                $request->inventory_item_id
            );
        }

        if ($request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        $summary = $query
            ->selectRaw('type, COUNT(*) as movement_count, SUM(quantity) as total_quantity')
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}