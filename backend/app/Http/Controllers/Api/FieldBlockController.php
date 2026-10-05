<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\FieldBlock;
use Illuminate\Http\Request;
use App\Services\GIS\GeometryService;

class FieldBlockController extends Controller
{
    public function index(Request $request)
    {
        $query = FieldBlock::with(['farm', 'currentCropCycle.crop', 'cropCycles' => fn ($q) => $q->latest()->limit(5)])
            ->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        return response()->json($query->latest()->paginate($request->integer('per_page', 50)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'boundary' => 'nullable|array',
            'area_hectares' => 'nullable|numeric|min:0.01',
            'area_unit' => 'nullable|string|max:20',
            'soil_type' => 'nullable|string|max:100',
            'irrigation_type' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,fallow,preparing,inactive',
        ]);

        Farm::where('id', $validated['farm_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $boundary = $validated['boundary'] ?? [];
        $area = $validated['area_hectares'];
        if (!empty($boundary)) {
            $geo = app(GeometryService::class);
            $norm = $geo->normalizePolygon($boundary);
            $pts = $geo->toLatLngPoints($norm);
            if (count($pts) >= 3) {
                $boundary = $norm;
                $area = $geo->hectares($geo->areaSquareMeters($pts));
            }
        }

        $block = FieldBlock::create([
            'farm_id' => $validated['farm_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'boundary' => $boundary,
            'area_hectares' => $area,
            'area_unit' => $validated['area_unit'] ?? 'hectares',
            'soil_type' => $validated['soil_type'] ?? null,
            'irrigation_type' => $validated['irrigation_type'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json($block->load('farm'), 201);
    }

    public function show(FieldBlock $fieldBlock)
    {
        $this->authorizeFarmOwnership($fieldBlock->farm_id);

        return response()->json(
            $fieldBlock->load([
                'farm',
                'cropCycles.crop',
                'cropCycles.variety',
                'currentCropCycle.crop',
                'activities' => fn ($q) => $q->latest()->limit(10),
            ])
        );
    }

    public function update(Request $request, FieldBlock $fieldBlock)
    {
        $this->authorizeFarmOwnership($fieldBlock->farm_id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'boundary' => 'nullable|array',
            'area_hectares' => 'sometimes|required|numeric|min:0.01',
            'area_unit' => 'nullable|string|max:20',
            'soil_type' => 'nullable|string|max:100',
            'irrigation_type' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,fallow,preparing,inactive',
        ]);

        $fieldBlock->update($validated);

        return response()->json([
            'message' => 'Field block updated successfully.',
            'data' => $fieldBlock->fresh()->load('farm'),
        ]);
    }

    public function destroy(FieldBlock $fieldBlock)
    {
        $this->authorizeFarmOwnership($fieldBlock->farm_id);
        $fieldBlock->delete();

        return response()->json(['message' => 'Field block deleted successfully.']);
    }

    protected function authorizeFarmOwnership(int $farmId): void
    {
        $owns = Farm::where('id', $farmId)->where('owner_id', auth()->id())->exists();
        if (!$owns) {
            abort(403, 'You do not own this field block.');
        }
    }

    /**
     * Set / replace field block boundary polygon.
     */
    public function updateBoundary(Request $request, FieldBlock $fieldBlock, GeometryService $geometry)
    {
        $this->authorizeFarmOwnership($fieldBlock->farm_id);

        $validated = $request->validate([
            'boundary' => 'required|array',
        ]);

        $geo = $geometry->normalizePolygon($validated['boundary']);
        $points = $geometry->toLatLngPoints($geo);
        $check = $geometry->validate($points);
        if (!$check['valid']) {
            return response()->json(['message' => $check['message']], 422);
        }

        $ha = $geometry->hectares($geometry->areaSquareMeters($points));
        $fieldBlock->update([
            'boundary' => $geo,
            'area_hectares' => $ha,
        ]);

        return response()->json([
            'message' => 'Field block boundary saved',
            'field_block' => $fieldBlock->fresh(),
            'area_hectares' => $ha,
        ]);
    }
}
