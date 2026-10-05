<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemonstrationFarm;
use Illuminate\Http\Request;

class DemonstrationFarmController extends Controller
{
    /**
     * Display a listing of demonstration farms.
     */
    public function index(Request $request)
    {
        $query = DemonstrationFarm::with(['institution', 'researcher.user']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('focus_crops_or_livestock', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('institution_id')) {
            $query->where('research_institution_id', $request->input('institution_id'));
        }

        if ($request->has('researcher_id')) {
            $query->where('researcher_id', $request->input('researcher_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Store a newly created demonstration farm.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'research_institution_id' => 'nullable|exists:research_institutions,id',
            'researcher_id' => 'nullable|exists:researchers,id',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'size_in_acres' => 'nullable|numeric|min:0',
            'focus_crops_or_livestock' => 'nullable|string|max:255',
            'technologies_demonstrated' => 'nullable|string',
            'status' => 'nullable|string|in:active,planned,completed,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'image_path' => 'nullable|string|max:255',
        ]);

        $demoFarm = DemonstrationFarm::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Demonstration farm created successfully.',
            'data' => $demoFarm->load(['institution', 'researcher.user']),
        ], 201);
    }

    /**
     * Display the specified demonstration farm.
     */
    public function show($id)
    {
        $demoFarm = DemonstrationFarm::with(['institution', 'researcher.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $demoFarm,
        ]);
    }

    /**
     * Update the specified demonstration farm.
     */
    public function update(Request $request, $id)
    {
        $demoFarm = DemonstrationFarm::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'research_institution_id' => 'nullable|exists:research_institutions,id',
            'researcher_id' => 'nullable|exists:researchers,id',
            'location' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'size_in_acres' => 'nullable|numeric|min:0',
            'focus_crops_or_livestock' => 'nullable|string|max:255',
            'technologies_demonstrated' => 'nullable|string',
            'status' => 'nullable|string|in:active,planned,completed,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'image_path' => 'nullable|string|max:255',
        ]);

        $demoFarm->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Demonstration farm updated successfully.',
            'data' => $demoFarm->load(['institution', 'researcher.user']),
        ]);
    }

    /**
     * Remove the specified demonstration farm.
     */
    public function destroy($id)
    {
        $demoFarm = DemonstrationFarm::findOrFail($id);
        $demoFarm->delete();

        return response()->json([
            'success' => true,
            'message' => 'Demonstration farm deleted successfully.',
        ]);
    }
}
