<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Requests\UpdateDiseaseRequest;
use App\Models\Disease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Disease\DiseaseIntegrationService;

class DiseaseController extends Controller
{
    /**
     * Display a listing of diseases with search, category, target, and severity filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Disease::query();

        // Search by name, scientific name, or symptoms
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('scientific_name', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%");
            });
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->byCategory($request->input('category'));
        }

        // Filter by Target (e.g., Maize, Cattle)
        if ($request->filled('target')) {
            $query->byTarget($request->input('target'));
        }

        // Filter by Severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        // Filter by Reportable status
        if ($request->has('reportable')) {
            $query->where('reportable', $request->boolean('reportable'));
        }

        $diseases = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $diseases,
        ]);
    }

    /**
     * Store a newly created disease in storage.
     */
    public function store(StoreDiseaseRequest $request): JsonResponse
    {
        $disease = Disease::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease record created successfully.',
            'data' => $disease,
        ], 201);
    }

    /**
     * Display the specified disease record.
     */
    public function show(int $id): JsonResponse
    {
        $disease = Disease::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $disease,
        ]);
    }

    /**
     * Update the specified disease record in storage.
     */
    public function update(UpdateDiseaseRequest $request, int $id): JsonResponse
    {
        $disease = Disease::findOrFail($id);
        $disease->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease record updated successfully.',
            'data' => $disease,
        ]);
    }

    /**
     * Remove the specified disease record from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $disease = Disease::findOrFail($id);
        $disease->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease record deleted successfully.',
        ]);
    }
    
    public function recommendations(
    DiseaseReport $report,
    DiseaseIntegrationService $service
)
{
    return response()->json([

        'products' =>

            $service->recommendedProducts($report),

        'extension_officers' =>

            $service->nearbyExtensionOfficers($report),

        'research' =>

            $service->relatedResearch($report),

        'active_loans' =>

            $service->activeLoans($report),

    ]);
}
}
