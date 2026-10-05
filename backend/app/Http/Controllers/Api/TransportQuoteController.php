<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TransportQuote;
use App\Services\Logistics\TransportQuoteService;
use Illuminate\Http\Request;

class TransportQuoteController extends Controller
{
    public function forOrder(Request $request, Order $order, TransportQuoteService $quotes)
    {
        $user = auth()->user();
        if ((int) $order->buyer_id !== (int) $user->id
            && (int) ($order->seller_id ?? 0) !== (int) $user->id
            && ($user->role ?? '') !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $result = $quotes->quoteForOrder($order, $request->only([
            'pickup_lat', 'pickup_lng', 'pickup_region',
            'dropoff_lat', 'dropoff_lng', 'delivery_region', 'limit',
        ]));

        return response()->json($result);
    }

    public function select(Request $request, Order $order)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'transporter_id' => 'required|exists:transporters,id',
        ]);

        if ((int) $order->buyer_id !== (int) auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Only buyer can select transporter'], 403);
        }

        TransportQuote::where('order_id', $order->id)->update(['status' => 'suggested']);
        $q = TransportQuote::where('order_id', $order->id)
            ->where('vehicle_id', $data['vehicle_id'])
            ->first();

        if ($q) {
            $q->update(['status' => 'selected']);
        }

        // Soft-link on order if columns exist
        try {
            $order->update([
                'transporter_id' => $data['transporter_id'],
                'vehicle_id' => $data['vehicle_id'],
                'transport_status' => 'quoted',
            ]);
        } catch (\Throwable $e) {
            // columns may not exist
        }

        return response()->json(['message' => 'Transporter selected', 'quote' => $q]);
    }
}
