<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\FarmBoundary;
use App\Services\GIS\GeometryService;
use Illuminate\Http\Request;

class FarmBoundaryController extends Controller
{
    public function show(Farm $farm)
    {
        $this->authorizeOwner($farm);
        $boundary = FarmBoundary::where('farm_id', $farm->id)->first();

        return response()->json([
            'farm_id' => $farm->id,
            'boundary' => $boundary,
            'farm_center' => [
                'latitude' => $farm->latitude,
                'longitude' => $farm->longitude,
            ],
        ]);
    }

    /**
     * Create or replace farm boundary (GeoJSON polygon).
     */
    public function store(Request $request, Farm $farm, GeometryService $geometry)
    {
        $this->authorizeOwner($farm);

        $validated = $request->validate([
            'boundary' => 'required|array',
        ]);

        $geo = $geometry->normalizePolygon($validated['boundary']);
        $points = $geometry->toLatLngPoints($geo);
        $check = $geometry->validate($points);
        if (!$check['valid']) {
            return response()->json(['message' => $check['message']], 422);
        }

        $areaM2 = $geometry->areaSquareMeters($points);
        $ha = $geometry->hectares($areaM2);
        $centroid = $geometry->centroid($points);
        $perimeter = $geometry->perimeterMeters($points);

        $boundary = FarmBoundary::updateOrCreate(
            ['farm_id' => $farm->id],
            [
                'boundary' => $geo,
                'area_hectares' => $ha,
                'perimeter_meters' => $perimeter,
                'centroid_latitude' => $centroid['lat'],
                'centroid_longitude' => $centroid['lng'],
            ]
        );

        // Keep farm lat/lng in sync with centroid for list/search
        $farm->update([
            'latitude' => $centroid['lat'],
            'longitude' => $centroid['lng'],
            'total_area_hectares' => $farm->total_area_hectares ?: $ha,
        ]);

        return response()->json([
            'message' => 'Farm boundary saved',
            'boundary' => $boundary,
            'area_hectares' => $ha,
            'centroid' => $centroid,
        ], 201);
    }

    public function destroy(Farm $farm)
    {
        $this->authorizeOwner($farm);
        FarmBoundary::where('farm_id', $farm->id)->delete();

        return response()->json(['message' => 'Farm boundary removed']);
    }

    protected function authorizeOwner(Farm $farm): void
    {
        $user = auth()->user();
        if (($user->role ?? '') === 'admin') {
            return;
        }
        if ((int) $farm->owner_id !== (int) $user->id) {
            abort(403, 'You do not own this farm.');
        }
    }
}
