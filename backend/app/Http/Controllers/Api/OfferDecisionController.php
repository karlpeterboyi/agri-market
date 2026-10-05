<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Order;
use App\Services\Marketplace\EscrowService;
use Illuminate\Http\Request;

class OfferDecisionController extends Controller
{
    public function accept($id)
    {
        $offer = Offer::with('listing')->findOrFail($id);

        // only listing owner can accept
        if ($offer->listing->seller_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($offer->status !== 'pending') {
            return response()->json(['message' => 'Offer already processed'], 422);
        }

        $offer->update(['status' => 'accepted']);

        $escrow = app(EscrowService::class);
        $total = $offer->quantity * $offer->offered_price;
        $order = Order::create([
            'offer_id' => $offer->id,
            'buyer_id' => $offer->buyer_id,
            'seller_id' => auth()->id(),
            'listing_id' => $offer->listing_id,
            'marketplace_type' => 'product',
            'marketplace_id' => $offer->listing_id,
            'quantity' => $offer->quantity,
            'price' => $offer->offered_price,
            'total_amount' => $total,
            'platform_fee' => $escrow->platformFee((float) $total),
            'seller_net' => $escrow->sellerNet((float) $total),
            'status' => 'pending_payment',
        ]);

        return response()->json([
            'message' => 'Offer accepted and order created',
            'order' => $order
        ]);
    }

    public function reject($id)
    {
        $offer = Offer::with('listing')->findOrFail($id);

        if ($offer->listing->seller_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $offer->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Offer rejected'
        ]);
    }
}