<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\Support\RoleAccess;

class FarmController extends Controller
{
    public function index()
    {
        $farms = Farm::query()
            ->where('owner_id', auth()->id())
            ->withCount('fieldBlocks')
            ->latest()
            ->paginate(15);

        return FarmResource::collection($farms);
    }

    public function store(StoreFarmRequest $request)
    {
        RoleAccess::assertCanSellProduce(auth()->user()); // farmers + admin

        $farm = Farm::create(array_merge(
            $request->validated(),
            [
                'owner_id' => auth()->id(),
            ]
        ));

        return new FarmResource($farm);
    }

    public function show(Farm $farm)
    {
        if ((int) $farm->owner_id !== (int) auth()->id() && (auth()->user()->role ?? '') !== 'admin') {
            abort(403, 'You do not own this farm.');
        }

        try {
            $farm->load(['fieldBlocks']);
        } catch (\Throwable $e) {
            // field blocks optional if schema lag
        }

        try {
            $farm->load('boundary');
        } catch (\Throwable $e) {
            // farm_boundaries table may be missing until migrate
        }

        // Plain JSON so mobile clients always see top-level id
        return response()->json([
            'id' => $farm->id,
            'owner_id' => $farm->owner_id,
            'farm_code' => $farm->farm_code,
            'name' => $farm->name,
            'description' => $farm->description,
            'farm_type' => $farm->farm_type,
            'ownership_type' => $farm->ownership_type,
            'country' => $farm->country,
            'region' => $farm->region,
            'district' => $farm->district,
            'ward' => $farm->ward,
            'village' => $farm->village,
            'address' => $farm->address,
            'latitude' => $farm->latitude,
            'longitude' => $farm->longitude,
            'elevation' => $farm->elevation,
            'total_area_hectares' => $farm->total_area_hectares,
            'cultivated_area_hectares' => $farm->cultivated_area_hectares,
            'irrigated_area_hectares' => $farm->irrigated_area_hectares,
            'status' => $farm->status,
            'field_blocks' => $farm->fieldBlocks ?? [],
            'boundary' => $farm->boundary,
            'created_at' => $farm->created_at,
            'updated_at' => $farm->updated_at,
        ]);
    }

    public function update(
        UpdateFarmRequest $request,
        Farm $farm
    ) {
        $this->authorize('update', $farm);

        $farm->update(
            $request->validated()
        );

        return new FarmResource($farm);
    }

    public function destroy(Farm $farm)
    {
        $this->authorize('delete', $farm);

        $farm->delete();

        return response()->json([
            'message' => 'Farm deleted successfully.'
        ]);
    }
}