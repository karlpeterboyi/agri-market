<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Researcher;
use Illuminate\Http\Request;

class ResearcherController extends Controller
{
    /**
     * Display a listing of researchers.
     */
    public function index(Request $request)
    {
        $query = Researcher::with(['user', 'institution'])->withCount('publications');

        if ($request->has('institution_id')) {
            $query->where('research_institution_id', $request->input('institution_id'));
        }

        if ($request->has('field_of_study')) {
            $query->where('field_of_study', 'like', '%' . $request->input('field_of_study') . '%');
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Store a newly created researcher profile.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:researchers,user_id',
            'research_institution_id' => 'nullable|exists:research_institutions,id',
            'title' => 'nullable|string|max:100',
            'field_of_study' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'orcid_id' => 'nullable|string|max:50|unique:researchers,orcid_id',
            'qualification' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'is_verified' => 'boolean',
        ]);

        $researcher = Researcher::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Researcher profile created successfully.',
            'data' => $researcher->load(['user', 'institution']),
        ], 201);
    }

    /**
     * Display the specified researcher profile with publications.
     */
    public function show($id)
    {
        $researcher = Researcher::with(['user', 'institution', 'publications', 'demonstrationFarms'])
            ->withCount('publications')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $researcher,
        ]);
    }

    /**
     * Update the specified researcher profile.
     */
    public function update(Request $request, $id)
    {
        $researcher = Researcher::findOrFail($id);

        $validated = $request->validate([
            'research_institution_id' => 'nullable|exists:research_institutions,id',
            'title' => 'nullable|string|max:100',
            'field_of_study' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'orcid_id' => 'nullable|string|max:50|unique:researchers,orcid_id,' . $id,
            'qualification' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'is_verified' => 'boolean',
        ]);

        $researcher->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Researcher profile updated successfully.',
            'data' => $researcher->load(['user', 'institution']),
        ]);
    }

    /**
     * Remove the specified researcher profile.
     */
    public function destroy($id)
    {
        $researcher = Researcher::findOrFail($id);
        $researcher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Researcher profile deleted successfully.',
        ]);
    }
}
