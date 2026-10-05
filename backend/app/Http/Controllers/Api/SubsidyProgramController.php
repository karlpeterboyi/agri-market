<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubsidyProgram;
use Illuminate\Http\Request;

class SubsidyProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = SubsidyProgram::query();

        // Public sees only open/active; officers see all
        if (!in_array(auth()->user()->role ?? 'guest', ['admin', 'government_officer'])) {
            $query->open();
        }

        if ($request->filled('program_type')) {
            $query->where('program_type', $request->program_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('region')) {
            $query->whereJsonContains('target_regions', $request->region);
        }

        return response()->json(
            $query->latest()->paginate(15)
        );
    }

    public function show(SubsidyProgram $subsidyProgram)
    {
        return response()->json(
            $subsidyProgram->loadCount('applications')
        );
    }

    public function store(Request $request)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subsidy_programs,code',
            'program_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'implementing_agency' => 'nullable|string|max:255',
            'budget_total' => 'nullable|numeric|min:0',
            'unit_value' => 'nullable|numeric|min:0',
            'unit_label' => 'nullable|string|max:50',
            'max_units_per_farmer' => 'nullable|integer|min:1',
            'eligibility_rules' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'target_regions' => 'nullable|array',
            'target_crops' => 'nullable|array',
            'application_start' => 'nullable|date',
            'application_end' => 'nullable|date|after_or_equal:application_start',
            'season_start' => 'nullable|date',
            'season_end' => 'nullable|date',
            'status' => 'nullable|in:draft,open,closed,completed',
            'is_active' => 'boolean',
        ]);

        $program = SubsidyProgram::create([
            ...$validated,
            'created_by' => auth()->id(),
            'status' => $validated['status'] ?? 'draft',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($program, 201);
    }

    public function update(Request $request, SubsidyProgram $subsidyProgram)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'program_type' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'implementing_agency' => 'nullable|string|max:255',
            'budget_total' => 'nullable|numeric|min:0',
            'unit_value' => 'nullable|numeric|min:0',
            'unit_label' => 'nullable|string|max:50',
            'max_units_per_farmer' => 'nullable|integer|min:1',
            'eligibility_rules' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'target_regions' => 'nullable|array',
            'target_crops' => 'nullable|array',
            'application_start' => 'nullable|date',
            'application_end' => 'nullable|date',
            'season_start' => 'nullable|date',
            'season_end' => 'nullable|date',
            'status' => 'nullable|in:draft,open,closed,completed',
            'is_active' => 'boolean',
        ]);

        $subsidyProgram->update($validated);

        return response()->json($subsidyProgram->fresh());
    }

    public function destroy(SubsidyProgram $subsidyProgram)
    {
        $this->authorizeGovernment();
        $subsidyProgram->delete();
        return response()->json(['message' => 'Subsidy program deleted.']);
    }

    protected function authorizeGovernment(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'government_officer'])) {
            abort(403);
        }
    }
}
