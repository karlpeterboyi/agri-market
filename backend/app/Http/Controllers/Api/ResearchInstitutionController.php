<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResearchInstitution;
use Illuminate\Http\Request;

class ResearchInstitutionController extends Controller
{
    /**
     * Display a listing of research institutions.
     */
    public function index(Request $request)
    {
        $query = ResearchInstitution::withCount(['researchers', 'publications', 'demonstrationFarms']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
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
     * Store a newly created research institution.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:research_institutions,code',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'is_verified' => 'boolean',
        ]);

        $institution = ResearchInstitution::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Research institution created successfully.',
            'data' => $institution,
        ], 201);
    }

    /**
     * Display the specified research institution with related researchers and publications.
     */
    public function show($id)
    {
        $institution = ResearchInstitution::with(['researchers.user', 'publications', 'demonstrationFarms'])
            ->withCount(['researchers', 'publications', 'demonstrationFarms'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $institution,
        ]);
    }

    /**
     * Update the specified research institution.
     */
    public function update(Request $request, $id)
    {
        $institution = ResearchInstitution::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:research_institutions,code,' . $id,
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'is_verified' => 'boolean',
        ]);

        $institution->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Research institution updated successfully.',
            'data' => $institution,
        ]);
    }

    /**
     * Remove the specified research institution.
     */
    public function destroy($id)
    {
        $institution = ResearchInstitution::findOrFail($id);
        $institution->delete();

        return response()->json([
            'success' => true,
            'message' => 'Research institution deleted successfully.',
        ]);
    }
}
