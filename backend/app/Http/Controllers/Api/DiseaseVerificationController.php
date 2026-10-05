<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiseaseVerificationRequest;
use App\Http\Requests\UpdateDiseaseVerificationRequest;
use App\Models\DiseaseDiagnosis;
use App\Models\DiseaseVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiseaseVerificationController extends Controller
{
    /**
     * Display a listing of verifications.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DiseaseVerification::with(['diseaseDiagnosis.disease', 'extensionOfficer']);

        if ($request->has('confirmed')) {
            $query->where('confirmed', $request->boolean('confirmed'));
        }

        if ($request->filled('extension_officer_id')) {
            $query->byOfficer($request->input('extension_officer_id'));
        }

        $verifications = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $verifications,
        ]);
    }

    /**
     * Store a newly created verification in storage.
     */
    public function store(StoreDiseaseVerificationRequest $request): JsonResponse
    {
        $verification = DiseaseVerification::create($request->validated());

        // Sync verification status back to the linked diagnosis
        $diagnosis = DiseaseDiagnosis::find($verification->disease_diagnosis);
        if ($diagnosis) {
            $diagnosis->update([
                'verified_by_expert' => $verification->confirmed,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Disease verification recorded successfully.',
            'data' => $verification->load(['diseaseDiagnosis', 'extensionOfficer']),
        ], 201);
    }

    /**
     * Display the specified verification record.
     */
    public function show(int $id): JsonResponse
    {
        $verification = DiseaseVerification::with(['diseaseDiagnosis.disease', 'extensionOfficer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $verification,
        ]);
    }

    /**
     * Update the specified verification in storage.
     */
    public function update(UpdateDiseaseVerificationRequest $request, int $id): JsonResponse
    {
        $verification = DiseaseVerification::findOrFail($id);
        $verification->update($request->validated());

        if ($request->has('confirmed')) {
            $diagnosis = DiseaseDiagnosis::find($verification->disease_diagnosis);
            if ($diagnosis) {
                $diagnosis->update([
                    'verified_by_expert' => $verification->confirmed,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Disease verification updated successfully.',
            'data' => $verification->load(['diseaseDiagnosis', 'extensionOfficer']),
        ]);
    }

    /**
     * Remove the specified verification from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $verification = DiseaseVerification::findOrFail($id);
        $verification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease verification deleted successfully.',
        ]);
    }
}
