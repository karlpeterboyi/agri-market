<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryReview;
use App\Models\MachineryListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineryReviewController extends Controller
{
    /**
     * Reviews for a machinery listing
     */
    public function index(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:machinery_listings,id'
        ]);

        return MachineryReview::with('user')
            ->where('machinery_listing_id', $request->listing_id)
            ->latest()
            ->get();
    }

    /**
     * Store review
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'machinery_listing_id' => 'required|exists:machinery_listings,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        $listing = MachineryListing::findOrFail($data['machinery_listing_id']);

        $alreadyReviewed = MachineryReview::where([
            'machinery_listing_id' => $listing->id,
            'user_id' => Auth::id()
        ])->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'You have already reviewed this machinery.'
            ], 422);
        }

        $data['user_id'] = Auth::id();

        return MachineryReview::create($data);
    }

    /**
     * Show review
     */
    public function show(MachineryReview $machineryReview)
    {
        return $machineryReview->load('user');
    }

    /**
     * Update review
     */
    public function update(Request $request, MachineryReview $machineryReview)
    {
        abort_if(Auth::id() != $machineryReview->user_id, 403);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        $machineryReview->update($data);

        return $machineryReview;
    }

    /**
     * Delete review
     */
    public function destroy(MachineryReview $machineryReview)
    {
        abort_if(Auth::id() != $machineryReview->user_id, 403);

        $machineryReview->delete();

        return response()->json([
            'message' => 'Review deleted successfully.'
        ]);
    }

    /**
     * Machinery rating summary
     */
    public function summary(MachineryListing $machineryListing)
    {
        $reviews = $machineryListing->reviews();

        return response()->json([
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('rating'), 1),
            'five_star' => $reviews->clone()->where('rating', 5)->count(),
            'four_star' => $reviews->clone()->where('rating', 4)->count(),
            'three_star' => $reviews->clone()->where('rating', 3)->count(),
            'two_star' => $reviews->clone()->where('rating', 2)->count(),
            'one_star' => $reviews->clone()->where('rating', 1)->count(),
        ]);
    }
}