<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        return Wishlist::with([
            'product.category',
            'product.brand',
            'product.images'
        ])
        ->where('user_id', Auth::id())
        ->latest()
        ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'input_listing_id' => [
                'required',
                'exists:input_listings,id'
            ]
        ]);

        Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'input_listing_id' => $validated['input_listing_id']
        ]);

        return response()->json([
            'message' => 'Added to wishlist.'
        ]);
    }

    public function destroy(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Removed from wishlist.'
        ]);
    }
}