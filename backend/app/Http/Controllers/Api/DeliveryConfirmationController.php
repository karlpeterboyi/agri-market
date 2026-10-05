<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\SettlementService;

class DeliveryConfirmationController extends Controller
{
    public function confirm($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->buyer_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ],403);
        }

        $payment = Payment::where(
            'order_id',
            $order->id
        )->firstOrFail();

        $result = SettlementService::releaseEscrow(
    $payment
);

        return response()->json([
    'message' => 'Delivery confirmed. Escrow released.',
    'commission' => $result['commission'],
    'seller_amount' => $result['seller_amount'],
    'order' => $result['order'],
    'payment' => $result['payment']
]);
    }
}