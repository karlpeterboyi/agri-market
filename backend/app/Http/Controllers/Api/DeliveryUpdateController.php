<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryUpdate;
use App\Models\TransportAssignment;
use Illuminate\Http\Request;

class DeliveryUpdateController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'transport_assignment_id' => 'required|exists:transport_assignments,id',
        'status' => 'required|in:accepted,picked_up,in_transit,delivered,cancelled',
        'notes' => 'nullable|string'
    ]);

    $assignment = TransportAssignment::findOrFail(
        $validated['transport_assignment_id']
    );

    // Prevent illegal transitions
    $allowedTransitions = [
        'assigned' => ['accepted', 'cancelled'],
        'accepted' => ['picked_up', 'cancelled'],
        'picked_up' => ['in_transit'],
        'in_transit' => ['delivered'],
    ];

    if (!isset($allowedTransitions[$assignment->status]) ||
        !in_array($validated['status'], $allowedTransitions[$assignment->status])) {
        return response()->json([
            'message' => 'Invalid status transition',
            'current_status' => $assignment->status
        ], 422);
    }

    $assignment->update([
        'status' => $validated['status']
    ]);

    $update = $assignment->deliveryUpdates()->create([
        'status' => $validated['status'],
        'notes' => $validated['notes']
    ]);

    return response()->json($update, 201);
}

    public function tracking($assignmentId)
    {
        return DeliveryUpdate::where(
            'transport_assignment_id',
            $assignmentId
        )
        ->orderBy('created_at')
        ->get();
    }
}