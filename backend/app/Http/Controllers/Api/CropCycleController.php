<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCropCycleRequest;
use App\Http\Resources\CropCycleResource;
use App\Models\CropCycle;
use App\Models\Farm;
use Illuminate\Http\Request;

class CropCycleController extends Controller
{
    public function index(Request $request)
    {
        $query = CropCycle::with(['farm', 'fieldBlock', 'crop', 'variety'])
            ->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        if ($request->filled('field_block_id')) {
            $query->where('field_block_id', $request->field_block_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('season')) {
            $query->where('season', $request->season);
        }

        return CropCycleResource::collection(
            $query->latest('planting_date')->paginate(20)
        );
    }

    public function store(StoreCropCycleRequest $request)
    {
        $data = $request->validated();

        $block = \App\Models\FieldBlock::with('farm')
            ->where('id', $data['field_block_id'])
            ->firstOrFail();

        if (!$block->farm || $block->farm->owner_id !== auth()->id()) {
            abort(403, 'You do not own this field block.');
        }

        // Architecture: CropCycle always hangs under FieldBlock; farm_id denormalized
        $data['farm_id'] = $block->farm_id;
        $data['field_block_id'] = $block->id;

        if (!empty($data['notes'])) {
            $data['metadata'] = array_merge($data['metadata'] ?? [], ['notes' => $data['notes']]);
            unset($data['notes']);
        }

        $cycle = CropCycle::create([
            ...$data,
            'status' => $data['status'] ?? 'planned',
        ]);

        return (new CropCycleResource($cycle->load(['farm', 'fieldBlock', 'crop', 'variety'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(CropCycle $cropCycle)
    {
        $this->authorizeOwnership($cropCycle);

        return new CropCycleResource(
            $cropCycle->load([
                'farm',
                'fieldBlock',
                'crop',
                'variety',
                'activities' => fn ($q) => $q->latest()->limit(20),
            ])
        );
    }

    public function update(Request $request, CropCycle $cropCycle)
    {
        $this->authorizeOwnership($cropCycle);

        $validated = $request->validate([
            'field_block_id' => 'nullable|exists:field_blocks,id',
            'crop_id' => 'sometimes|required|exists:crops,id',
            'crop_variety_id' => 'nullable|exists:crop_varieties,id',
            'season' => 'sometimes|required|string|max:100',
            'planting_date' => 'sometimes|required|date',
            'expected_harvest_date' => 'nullable|date',
            'actual_harvest_date' => 'nullable|date',
            'area_hectares' => 'sometimes|required|numeric|min:0.01',
            'expected_yield' => 'nullable|numeric|min:0',
            'actual_yield' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:planned,planted,growing,harvesting,completed,failed,abandoned',
            'metadata' => 'nullable|array',
        ]);

        $cropCycle->update($validated);

        return new CropCycleResource($cropCycle->fresh()->load(['farm', 'fieldBlock', 'crop', 'variety']));
    }

    public function destroy(CropCycle $cropCycle)
    {
        $this->authorizeOwnership($cropCycle);

        $cropCycle->delete();

        return response()->json(['message' => 'Crop cycle deleted successfully.']);
    }

    /**
     * Mark a crop cycle as harvested and record actual yield.
     */
    public function harvest(Request $request, CropCycle $cropCycle)
    {
        $this->authorizeOwnership($cropCycle);

        $validated = $request->validate([
            'actual_harvest_date' => 'required|date',
            'actual_yield' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $cropCycle->update([
            'actual_harvest_date' => $validated['actual_harvest_date'],
            'actual_yield' => $validated['actual_yield'],
            'status' => 'completed',
            'metadata' => array_merge($cropCycle->metadata ?? [], [
                'harvest_notes' => $validated['notes'] ?? null,
            ]),
        ]);

        return new CropCycleResource($cropCycle->fresh()->load(['crop', 'variety']));
    }

    protected function authorizeOwnership(CropCycle $cropCycle): void
    {
        if ($cropCycle->farm->owner_id !== auth()->id()) {
            abort(403, 'You do not own this crop cycle.');
        }
    }
}
