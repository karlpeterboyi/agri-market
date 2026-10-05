<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmVisitController extends Controller
{
    /**
     * POST /api/farm-visits
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'advisory_request_id' => 'required|exists:advisory_requests,id',
            'scheduled_at' => 'required|date|after:now',
            'visit_fee' => 'required|numeric|min:0',
        ]);

        $visit = FarmVisit::create([
            'advisory_request_id' => $validated['advisory_request_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'visit_fee' => $validated['visit_fee'],
            'status' => FarmVisit::STATUS_SCHEDULED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Farm visit scheduled successfully.',
            'data' => $visit,
        ], 201);
    }
}
