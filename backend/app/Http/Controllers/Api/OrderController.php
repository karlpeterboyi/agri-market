<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InputListing;
use App\Models\LogisticsRequest;
use App\Models\MachineryListing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductListing;
use App\Services\Marketplace\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Unified marketplace order flow (same path for produce, inputs, machinery):
 * List → Buy/Offer → Order (pending_payment) → Pay (escrow) → Logistics →
 * Buyer confirms delivery → Release 90% seller / 10% platform.
 */
class OrderController extends Controller
{
    public function store(Request $request, EscrowService $escrow)
    {
        $validated = $request->validate([
            // product (crop) | input | machinery
            'marketplace_type'  => 'nullable|in:product,input,machinery',
            'listing_id'        => 'nullable|integer',
            'marketplace_id'    => 'nullable|integer',
            'quantity'          => 'required|numeric|min:0.01',
            'pickup_region'     => 'nullable|string|max:255',
            'pickup_district'   => 'nullable|string|max:255',
            'delivery_region'   => 'nullable|string|max:255',
            'delivery_district' => 'nullable|string|max:255',
        ]);

        $type = $validated['marketplace_type'] ?? 'product';
        $sourceId = $validated['marketplace_id'] ?? $validated['listing_id'] ?? null;
        if (!$sourceId) {
            return response()->json(['message' => 'listing_id or marketplace_id is required'], 422);
        }

        [$sellerId, $unitPrice, $title, $productListingId] = $this->resolveListing($type, (int) $sourceId);

        if ($sellerId === auth()->id()) {
            return response()->json(['message' => 'You cannot buy your own listing.'], 422);
        }

        $qty = (float) $validated['quantity'];
        $total = round($unitPrice * $qty, 2);
        $fee = $escrow->platformFee($total);
        $net = $escrow->sellerNet($total);

        $order = DB::transaction(function () use (
            $validated, $type, $sourceId, $sellerId, $unitPrice, $title,
            $productListingId, $qty, $total, $fee, $net
        ) {
            $order = Order::create([
                'offer_id'          => null,
                'buyer_id'          => auth()->id(),
                'seller_id'         => $sellerId,
                'listing_id'        => $productListingId,
                'marketplace_type'  => $type,
                'marketplace_id'    => $sourceId,
                'title'             => $title,
                'quantity'          => $qty,
                'price'             => $unitPrice,
                'total_amount'      => $total,
                'platform_fee'      => $fee,
                'seller_net'        => $net,
                'status'            => 'pending_payment',
            ]);

            // Logistics for physical goods (product, input, machinery)
            if (in_array($type, ['product', 'input', 'machinery'], true)) {
                LogisticsRequest::create([
                    'order_id'          => $order->id,
                    'listing_id'        => $productListingId, // may be null for non-product
                    'buyer_id'          => auth()->id(),
                    'pickup_region'     => $validated['pickup_region'] ?? 'Tanzania',
                    'pickup_district'   => $validated['pickup_district'] ?? 'Unknown',
                    'delivery_region'   => $validated['delivery_region'] ?? 'Tanzania',
                    'delivery_district' => $validated['delivery_district'] ?? 'Unknown',
                    'quantity'          => $qty,
                    'status'            => 'pending',
                ]);
            }

            return $order;
        });

        return response()->json(
            $order->load(['buyer', 'seller', 'listing', 'logisticsRequest', 'payment']),
            201
        );
    }

    public function index()
    {
        $user = auth()->user();

        $query = Order::with([
            'buyer',
            'seller',
            'listing.commodity',
            'logisticsRequest.assignment',
            'payment',
        ])->latest();

        if ($user->role === 'buyer') {
            $query->where('buyer_id', $user->id);
        } elseif (in_array($user->role, ['farmer', 'agrodealer', 'provider', 'processor'], true)) {
            $query->where('seller_id', $user->id);
        } elseif ($user->role !== 'admin') {
            $query->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            });
        }

        return response()->json($query->paginate(20));
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        if (
            $user->role !== 'admin'
            && $order->buyer_id !== $user->id
            && $order->seller_id !== $user->id
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $order->load([
            'buyer',
            'seller',
            'listing.commodity',
            'logisticsRequest.assignment',
            'payment',
            'offer',
        ]);
    }

    /**
     * Buyer confirms goods received → release escrow (90% seller, 10% platform).
     */
    public function confirmDelivery(Order $order, EscrowService $escrow)
    {
        if ($order->buyer_id != auth()->id() && (auth()->user()->role ?? '') !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status != 'in_escrow') {
            return response()->json([
                'message' => 'Order is not in escrow. Current status: '.$order->status,
            ], 400);
        }

        $payment = Payment::where('order_id', $order->id)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        $result = $escrow->release($order, $payment);

        // Mark logistics delivered if present
        if ($order->logisticsRequest && $order->logisticsRequest->status !== 'delivered') {
            $order->logisticsRequest->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Delivery confirmed. 90% released to seller, 10% platform fee retained by MkulimaHub. NMB payout initiated when configured.',
            'seller_net' => $result['seller_net'],
            'platform_fee' => $result['platform_fee'],
            'wallet_balance' => $result['seller_available'],
            'nmb_payout' => $result['nmb_payout'] ?? null,
            'order' => $order->fresh()->load('payment'),
        ]);
    }

    /**
     * @return array{0:int,1:float,2:string,3:?int} sellerId, unitPrice, title, productListingId
     */
    protected function resolveListing(string $type, int $id): array
    {
        return match ($type) {
            'input' => (function () use ($id) {
                $item = InputListing::findOrFail($id);
                abort_if(($item->status ?? '') === 'sold', 422, 'Listing not available');
                $price = (float) ($item->price ?? 0);
                return [(int) $item->seller_id, $price, $item->name ?? 'Input', null];
            })(),
            'machinery' => (function () use ($id) {
                $item = MachineryListing::findOrFail($id);
                abort_if(!($item->available ?? true) || in_array($item->status ?? '', ['sold', 'booked'], true), 422, 'Machinery not available');
                $price = (float) ($item->sale_price ?: $item->rental_price ?: 0);
                abort_if($price <= 0, 422, 'Listing has no price');
                return [(int) $item->owner_id, $price, $item->title ?? 'Machinery', null];
            })(),
            default => (function () use ($id) {
                $item = ProductListing::findOrFail($id);
                abort_if(($item->status ?? '') !== 'available' && ($item->status ?? '') !== 'active', 422, 'Listing not available');
                return [(int) $item->seller_id, (float) $item->price, $item->commodity->name ?? 'Produce', (int) $item->id];
            })(),
        };
    }
}
