<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\InputListing;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    /**
     * Return the authenticated user's cart.
     */
    public function index()
    {
        $cart = ShoppingCart::with([
            'items.product.category',
            'items.product.brand',
            'items.product.images'
        ])->firstOrCreate([
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'cart' => $cart,
            'items' => $cart->items,
            'total' => $cart->total()
        ]);
    }

    /**
     * Add an item to cart.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'input_listing_id' => ['required', 'exists:input_listings,id'],
            'quantity' => ['nullable', 'integer', 'min:1']
        ]);

        $quantity = $validated['quantity'] ?? 1;

        $cart = ShoppingCart::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $product = InputListing::findOrFail($validated['input_listing_id']);

        $item = CartItem::where('shopping_cart_id', $cart->id)
            ->where('input_listing_id', $product->id)
            ->first();

        if ($item) {

            $item->increment('quantity', $quantity);

        } else {

            CartItem::create([
                'shopping_cart_id' => $cart->id,
                'input_listing_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ]);

        }

        return response()->json([
            'message' => 'Item added to cart.'
        ]);
    }

    /**
     * Update quantity.
     */
    public function update(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1']
        ]);

        if ($item->cart->user_id !== Auth::id()) {

            abort(403);

        }

        $item->update([
            'quantity' => $validated['quantity']
        ]);

        return response()->json([
            'message' => 'Cart updated.'
        ]);
    }

    /**
     * Remove one item.
     */
    public function remove(CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) {

            abort(403);

        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed.'
        ]);
    }

    /**
     * Empty the cart.
     */
    public function clear()
    {
        $cart = ShoppingCart::where('user_id', Auth::id())->first();

        if ($cart) {

            $cart->items()->delete();

        }

        return response()->json([
            'message' => 'Cart cleared.'
        ]);
    }
}