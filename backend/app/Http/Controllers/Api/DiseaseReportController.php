<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiseaseReportRequest;
use App\Http\Requests\UpdateDiseaseReportRequest;
use App\Models\DiseaseReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiseaseReportController extends Controller
{
    /**
     * Display a listing of disease reports.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DiseaseReport::with(['user', 'disease', 'extensionOfficer']);

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->filled('commodity_type')) {
            $query->byCommodityType($request->input('commodity_type'));
        }

        if ($request->filled('region')) {
            $query->byLocation($request->input('region'), $request->input('district'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $reports = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Store a newly created disease report in storage.
     */
    public function store(StoreDiseaseReportRequest $request): JsonResponse
    {
        $report = DiseaseReport::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease report submitted successfully.',
            'data' => $report->load(['user', 'disease', 'extensionOfficer']),
        ], 201);
    }

    /**
     * Display the specified disease report.
     */
    public function show(int $id): JsonResponse
    {
        $report = DiseaseReport::with(['user', 'disease', 'extensionOfficer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Update the specified disease report.
     */
    public function update(UpdateDiseaseReportRequest $request, int $id): JsonResponse
    {
        $report = DiseaseReport::findOrFail($id);
        $report->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease report updated successfully.',
            'data' => $report->load(['user', 'disease', 'extensionOfficer']),
        ]);
    }

    /**
     * Remove the specified disease report.
     */
    public function destroy(int $id): JsonResponse
    {
        $report = DiseaseReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease report deleted successfully.',
        ]);
    }
}
