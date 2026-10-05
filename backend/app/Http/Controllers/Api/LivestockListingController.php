<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LivestockListing;
use Illuminate\Http\Request;

class LivestockListingController extends Controller
{
    /**
     * Display all livestock listings.
     */
    public function index(Request $request)
{
    $query = LivestockListing::with([
        'seller',
        'images'
    ]);

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('breed', 'ILIKE', '%' . $request->search . '%')
              ->orWhere('species', 'ILIKE', '%' . $request->search . '%')
              ->orWhere('description', 'ILIKE', '%' . $request->search . '%');
        });
    }

    if ($request->filled('species')) {
        $query->where('species', $request->species);
    }

    if ($request->filled('region')) {
        $query->where('region', $request->region);
    }

    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    if ($request->filled('gender')) {
        $query->where('gender', $request->gender);
    }

    if ($request->filled('purpose')) {
        $query->where('purpose', $request->purpose);
    }

    if ($request->filled('min_age')) {
        $query->where('age', '>=', $request->min_age);
    }

    if ($request->filled('max_age')) {
        $query->where('age', '<=', $request->max_age);
    }

    switch ($request->sort) {

        case 'price_low':
            $query->orderBy('price');
            break;

        case 'price_high':
            $query->orderByDesc('price');
            break;

        case 'oldest':
            $query->oldest();
            break;

        default:
            $query->latest();
    }

    return $query->paginate(12);
}
    /**
     * Store a new livestock listing.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'livestock_category_id'=>'required|exists:livestock_categories,id',

            'livestock_breed_id'=>'nullable|exists:livestock_breeds,id',

            'title'=>'required|string|max:255',

            'quantity'=>'required|integer|min:1',

            'age'=>'nullable|string',

            'weight'=>'nullable|string',

            'gender'=>'nullable|string',

            'price'=>'required|numeric',

            'region'=>'required|string',

            'district'=>'required|string',

            'description'=>'nullable|string',

            'status'=>'nullable|string'
        ]);

        $listing = LivestockListing::create([

            ...$validated,

            'seller_id'=>auth()->id(),

            'status'=>'available'
        ]);

        return response()->json($listing,201);
    }

    /**
     * Display one listing.
     */
    public function show($id)
{
    $listing = LivestockListing::with([
        'seller',
        'category',
        'breed',
        'images'
    ])->findOrFail($id);

    $related = LivestockListing::with([
            'images',
            'seller'
        ])
        ->where('category_id', $listing->category_id)
        ->where('id', '!=', $listing->id)
        ->latest()
        ->take(6)
        ->get();

    return response()->json([
        'listing' => $listing,
        'related' => $related,
    ]);
}

    /**
     * Update listing.
     */
    public function update(Request $request,$id)
    {
        $listing = LivestockListing::findOrFail($id);

        $listing->update($request->all());

        return response()->json($listing);
    }

    /**
     * Delete listing.
     */
    public function destroy($id)
    {
        LivestockListing::findOrFail($id)->delete();

        return response()->json([
            'message'=>'Listing deleted.'
        ]);
    }
}