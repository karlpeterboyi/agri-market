<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFarmActivityRequest;
use App\Models\Farm;
use App\Models\FarmActivity;
use Illuminate\Http\Request;

class FarmActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = FarmActivity::with(['farm', 'fieldBlock', 'cropCycle.crop', 'user'])
            ->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        if ($request->filled('field_block_id')) {
            $query->where('field_block_id', $request->field_block_id);
        }

        if ($request->filled('crop_cycle_id')) {
            $query->where('crop_cycle_id', $request->crop_cycle_id);
        }

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('activity_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('activity_date', '<=', $request->to_date);
        }

        return response()->json(
            $query->latest('activity_date')->paginate(25)
        );
    }

    public function store(StoreFarmActivityRequest $request)
    {
        $data = $request->validated();

        $farm = Farm::where('id', $data['farm_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $activity = FarmActivity::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return response()->json(
            $activity->load(['farm', 'fieldBlock', 'cropCycle', 'user']),
            201
        );
    }

    public function show(FarmActivity $farmActivity)
    {
        $this->authorizeOwnership($farmActivity);

        return response()->json(
            $farmActivity->load([
                'farm',
                'fieldBlock',
                'cropCycle.crop',
                'user',
                'attachments',
                'stockMovements.inventoryItem',
            ])
        );
    }

    public function update(Request $request, FarmActivity $farmActivity)
    {
        $this->authorizeOwnership($farmActivity);

        $validated = $request->validate([
            'activity_type' => 'sometimes|required|string|max:100',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'sometimes|required|date',
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'labour_cost' => 'nullable|numeric|min:0',
            'workers' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'metadata' => 'nullable|array',
        ]);

        $farmActivity->update($validated);

        return response()->json([
            'message' => 'Activity updated successfully.',
            'data' => $farmActivity->fresh()->load(['farm', 'fieldBlock', 'cropCycle']),
        ]);
    }

    public function destroy(FarmActivity $farmActivity)
    {
        $this->authorizeOwnership($farmActivity);

        $farmActivity->delete();

        return response()->json(['message' => 'Activity deleted successfully.']);
    }

    /**
     * Summary of costs for a farm or period.
     */
    public function costSummary(Request $request)
    {
        $query = FarmActivity::query()
            ->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('activity_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('activity_date', '<=', $request->to_date);
        }

        $summary = $query->selectRaw('
            activity_type,
            COUNT(*) as activity_count,
            COALESCE(SUM(cost), 0) as total_input_cost,
            COALESCE(SUM(labour_cost), 0) as total_labour_cost,
            COALESCE(SUM(cost + labour_cost), 0) as total_cost
        ')
        ->groupBy('activity_type')
        ->get();

        return response()->json([
            'summary_by_type' => $summary,
            'grand_total' => $summary->sum('total_cost'),
        ]);
    }

    protected function authorizeOwnership(FarmActivity $activity): void
    {
        if ($activity->farm->owner_id !== auth()->id()) {
            abort(403, 'You do not own this activity.');
        }
    }
}
