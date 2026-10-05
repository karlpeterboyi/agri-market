<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExtensionOfficerRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtensionOfficerRatingController extends Controller
{
    /**
     * POST /api/extension-ratings
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'extension_officer_id' => 'required|exists:extension_officers,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $rating = ExtensionOfficerRating::updateOrCreate(
            [
                'extension_officer_id' => $validated['extension_officer_id'],
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully.',
            'data' => $rating,
        ], 201);
    }
}
