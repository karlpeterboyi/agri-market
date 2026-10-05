<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiseaseOutbreakRequest;
use App\Http\Requests\UpdateDiseaseOutbreakRequest;
use App\Models\DiseaseOutbreak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiseaseOutbreakController extends Controller
{
    /**
     * Display a listing of outbreaks with filtering options.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DiseaseOutbreak::with('disease');

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('risk_level')) {
            $query->byRiskLevel($request->input('risk_level'));
        }

        if ($request->filled('region')) {
            $query->byLocation($request->input('region'), $request->input('district'));
        }

        if ($request->filled('disease_id')) {
            $query->where('disease_id', $request->input('disease_id'));
        }

        $outbreaks = $query->latest('reported_on')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $outbreaks,
        ]);
    }

    /**
     * Store a newly created outbreak record in storage.
     */
    public function store(StoreDiseaseOutbreakRequest $request): JsonResponse
    {
        $outbreak = DiseaseOutbreak::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease outbreak alert recorded successfully.',
            'data' => $outbreak->load('disease'),
        ], 201);
    }

    /**
     * Display the specified outbreak record.
     */
    public function show(int $id): JsonResponse
    {
        $outbreak = DiseaseOutbreak::with('disease')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $outbreak,
        ]);
    }

    /**
     * Update the specified outbreak in storage.
     */
    public function update(UpdateDiseaseOutbreakRequest $request, int $id): JsonResponse
    {
        $outbreak = DiseaseOutbreak::findOrFail($id);
        $outbreak->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease outbreak record updated successfully.',
            'data' => $outbreak->load('disease'),
        ]);
    }

    /**
     * Remove the specified outbreak from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $outbreak = DiseaseOutbreak::findOrFail($id);
        $outbreak->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease outbreak record deleted successfully.',
        ]);
    }
}
