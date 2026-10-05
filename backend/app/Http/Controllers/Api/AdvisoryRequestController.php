<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvisoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryRequestController extends Controller
{
    /**
     * POST /api/advisory-requests
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'extension_officer_id' => 'nullable|exists:extension_officers,id',
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        $advisoryRequest = AdvisoryRequest::create([
            'farmer_id' => $request->user()->id,
            'extension_officer_id' => $validated['extension_officer_id'] ?? null,
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? AdvisoryRequest::PRIORITY_NORMAL,
            'status' => isset($validated['extension_officer_id']) 
                ? AdvisoryRequest::STATUS_ASSIGNED 
                : AdvisoryRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advisory request submitted successfully.',
            'data' => $advisoryRequest,
        ], 201);
    }

    /**
     * GET /api/my-advisory-requests
     */
    public function myRequests(Request $request): JsonResponse
    {
        $requests = AdvisoryRequest::with('extensionOfficer.user:id,name')
            ->where('farmer_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * PUT /api/advisory-requests/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $advisoryRequest = AdvisoryRequest::find($id);

        if (!$advisoryRequest) {
            return response()->json(['success' => false, 'message' => 'Request not found'], 404);
        }

        $validated = $request->validate([
            'extension_officer_id' => 'nullable|exists:extension_officers,id',
            'status' => 'nullable|string|in:pending,assigned,in_progress,resolved,closed',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
        ]);

        $advisoryRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Advisory request updated successfully.',
            'data' => $advisoryRequest,
        ]);
    }
}
