<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogisticsRequest;
use Illuminate\Http\Request;

class LogisticsController extends Controller
{
    public function create(Request $request)
{
    $data = $request->validate([
        'order_id' => 'required|exists:orders,id',
        'listing_id' => 'required|exists:product_listings,id',

        'pickup_region' => 'required|string',
        'pickup_district' => 'required|string',

        'delivery_region' => 'required|string',
        'delivery_district' => 'required|string',

        'quantity' => 'required|numeric|min:1'
    ]);

    $data['buyer_id'] = auth()->id();
    $data['status'] = 'pending';

    $logistics = \App\Models\LogisticsRequest::create($data);

    return response()->json($logistics, 201);
}

    public function assign(Request $request, $id)
    {
        $request->validate([
            'transporter_id' => 'required|exists:transporters,id',
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $logistics = LogisticsRequest::findOrFail($id);

        $logistics->update([
            'status' => 'assigned'
        ]);

        // IMPORTANT: should be in transport_assignments table
        $logistics->assignment()->create([
            'transporter_id' => $request->transporter_id,
            'vehicle_id' => $request->vehicle_id,
            'status' => 'assigned'
        ]);

        return response()->json($logistics->load('assignment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_transit,delivered,cancelled'
        ]);

        $logistics = LogisticsRequest::findOrFail($id);

        $update = ['status' => $request->status];

        if ($request->status === 'in_transit') {
            $update['picked_at'] = now();
        }

        if ($request->status === 'delivered') {
            $update['delivered_at'] = now();
        }

        $logistics->update($update);

        return response()->json($logistics);
    }
}