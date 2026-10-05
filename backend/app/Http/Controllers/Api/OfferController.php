<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\ProductListing;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:product_listings,id',
            'offered_price' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:1',
            'message' => 'nullable|string',
        ]);

        $offer = Offer::create([
            ...$validated,
            'buyer_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json($offer, 201);
    }

    public function listingOffers($listingId)
    {
        return Offer::with('buyer')
            ->where('listing_id', $listingId)
            ->get();
    }

    public function accept($id)
    {
        $offer = Offer::findOrFail($id);

        $offer->update([
            'status' => 'accepted'
        ]);

        return response()->json([
            'message' => 'Offer accepted',
            'offer' => $offer
        ]);
    }

    public function reject($id)
    {
        $offer = Offer::findOrFail($id);

        $offer->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Offer rejected',
            'offer' => $offer
        ]);
    }
}