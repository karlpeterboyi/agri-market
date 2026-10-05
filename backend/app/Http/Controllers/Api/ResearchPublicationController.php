<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResearchPublication;
use Illuminate\Http\Request;

class ResearchPublicationController extends Controller
{
    /**
     * Display a listing of research publications.
     */
    public function index(Request $request)
    {
        $query = ResearchPublication::with(['researcher.user', 'institution']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('abstract', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%");
        }

        if ($request->has('researcher_id')) {
            $query->where('researcher_id', $request->input('researcher_id'));
        }

        if ($request->has('institution_id')) {
            $query->where('research_institution_id', $request->input('institution_id'));
        }

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('publication_date')->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Store a newly created research publication.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'researcher_id' => 'required|exists:researchers,id',
            'research_institution_id' => 'nullable|exists:research_institutions,id',
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'keywords' => 'nullable|string',
            'journal_name' => 'nullable|string|max:255',
            'doi' => 'nullable|string|max:255|unique:research_publications,doi',
            'document_path' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        $publication = ResearchPublication::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Research publication added successfully.',
            'data' => $publication->load(['researcher.user', 'institution']),
        ], 201);
    }

    /**
     * Display the specified research publication.
     */
    public function show($id)
    {
        $publication = ResearchPublication::with(['researcher.user', 'institution'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $publication,
        ]);
    }

    /**
     * Update the specified research publication.
     */
    public function update(Request $request, $id)
    {
        $publication = ResearchPublication::findOrFail($id);

        $validated = $request->validate([
            'researcher_id' => 'sometimes|required|exists:researchers,id',
            'research_institution_id' => 'nullable|exists:research_institutions,id',
            'title' => 'sometimes|required|string|max:255',
            'abstract' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'keywords' => 'nullable|string',
            'journal_name' => 'nullable|string|max:255',
            'doi' => 'nullable|string|max:255|unique:research_publications,doi,' . $id,
            'document_path' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        $publication->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Research publication updated successfully.',
            'data' => $publication->load(['researcher.user', 'institution']),
        ]);
    }

    /**
     * Remove the specified research publication.
     */
    public function destroy($id)
    {
        $publication = ResearchPublication::findOrFail($id);
        $publication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Research publication deleted successfully.',
        ]);
    }
}
