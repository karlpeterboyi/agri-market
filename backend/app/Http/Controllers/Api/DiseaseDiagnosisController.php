<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiseaseDiagnosisRequest;
use App\Models\DiseaseDiagnosis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiseaseDiagnosisController extends Controller
{
    /**
     * Display a listing of disease diagnoses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DiseaseDiagnosis::with(['diseaseReport', 'disease']);

        if ($request->has('verified')) {
            $query->where('verified_by_expert', $request->boolean('verified'));
        }

        if ($request->filled('min_confidence')) {
            $query->highConfidence((float) $request->input('min_confidence'));
        }

        if ($request->filled('disease_report_id')) {
            $query->where('disease_report_id', $request->input('disease_report_id'));
        }

        $diagnoses = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $diagnoses,
        ]);
    }

    /**
     * Store a newly created diagnosis (e.g. triggered by AI backend service).
     */
    public function store(StoreDiseaseDiagnosisRequest $request): JsonResponse
    {
        $diagnosis = DiseaseDiagnosis::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Disease diagnosis logged successfully.',
            'data' => $diagnosis->load(['diseaseReport', 'disease']),
        ], 201);
    }

    /**
     * Display the specified diagnosis.
     */
    public function show(int $id): JsonResponse
    {
        $diagnosis = DiseaseDiagnosis::with(['diseaseReport', 'disease'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $diagnosis,
        ]);
    }

    /**
     * Remove the specified diagnosis.
     */
    public function destroy(int $id): JsonResponse
    {
        $diagnosis = DiseaseDiagnosis::findOrFail($id);
        $diagnosis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease diagnosis deleted successfully.',
        ]);
    }
}
