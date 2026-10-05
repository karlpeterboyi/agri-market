<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransportAssignment;
use Illuminate\Validation\Rule;

class TransportAssignmentController extends Controller
{
    public function assign(Request $request)
{
    $validated = $request->validate([
        'logistics_request_id' => 'required|exists:logistics_requests,id',
        'transporter_id' => 'required|exists:transporters,id',
        'vehicle_id' => 'required|exists:vehicles,id',
    ]);

    $logistics = \App\Models\LogisticsRequest::findOrFail(
        $validated['logistics_request_id']
    );

    // Prevent assigning an already assigned/completed request
    if ($logistics->status !== 'pending') {
        return response()->json([
            'message' => 'This logistics request cannot be assigned.',
            'status' => $logistics->status,
        ], 422);
    }

    // Make sure the selected vehicle belongs to the selected transporter
    $vehicle = \App\Models\Vehicle::where('id', $validated['vehicle_id'])
        ->where('transporter_id', $validated['transporter_id'])
        ->first();

    if (!$vehicle) {
        return response()->json([
            'message' => 'Selected vehicle does not belong to the selected transporter.',
        ], 422);
    }

    // Prevent using an unavailable vehicle
    if (!$vehicle->available) {
        return response()->json([
            'message' => 'Selected vehicle is currently unavailable.',
        ], 422);
    }

    $assignment = TransportAssignment::create([
        'logistics_request_id' => $logistics->id,
        'transporter_id' => $validated['transporter_id'],
        'vehicle_id' => $validated['vehicle_id'],
        'status' => 'assigned',
    ]);

    $logistics->update([
        'status' => 'assigned',
    ]);

    // Mark vehicle as unavailable while assigned
    $vehicle->update([
        'available' => false,
    ]);

    return response()->json(
        $assignment->load([
            'logisticsRequest',
            'transporter',
            'vehicle',
        ]),
        201
    );
}

    /**
     * Update the status of a transport assignment
     */
    public function updateStatus(Request $request, $id)
{
    $assignment = TransportAssignment::with([
        'logisticsRequest',
        'vehicle'
    ])->findOrFail($id);

    $validated = $request->validate([
        'status' => [
            'required',
            Rule::in([
                'assigned',
                'accepted',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled'
            ])
        ]
    ]);

    $newStatus = $validated['status'];

    // Prevent invalid status transitions
    $allowedTransitions = [
        'assigned' => ['accepted', 'cancelled'],
        'accepted' => ['picked_up', 'cancelled'],
        'picked_up' => ['in_transit'],
        'in_transit' => ['delivered'],
    ];

    if (
        $assignment->status !== $newStatus &&
        (
            !isset($allowedTransitions[$assignment->status]) ||
            !in_array($newStatus, $allowedTransitions[$assignment->status])
        )
    ) {
        return response()->json([
            'message' => 'Invalid status transition.',
            'current_status' => $assignment->status,
            'requested_status' => $newStatus,
        ], 422);
    }

    $assignment->update([
        'status' => $newStatus,
    ]);

    // Keep logistics request status synchronized
    if ($assignment->logisticsRequest) {
        $logisticsStatus = match ($newStatus) {
            'assigned',
            'accepted',
            'picked_up' => 'assigned',

            'in_transit' => 'in_transit',

            'delivered' => 'delivered',

            'cancelled' => 'cancelled',

            default => $assignment->logisticsRequest->status,
        };

        $assignment->logisticsRequest->update([
            'status' => $logisticsStatus,
        ]);
    }

    // Release vehicle after completed/cancelled assignment
    if (
        in_array($newStatus, ['delivered', 'cancelled']) &&
        $assignment->vehicle
    ) {
        $assignment->vehicle->update([
            'available' => true,
        ]);
    }

    return response()->json(
        $assignment->fresh([
            'logisticsRequest',
            'transporter',
            'vehicle',
        ])
    );
}
    public function show($id)
    {
        return TransportAssignment::with([
            'logisticsRequest',
            'transporter',
            'vehicle'
        ])->findOrFail($id);
    }
}