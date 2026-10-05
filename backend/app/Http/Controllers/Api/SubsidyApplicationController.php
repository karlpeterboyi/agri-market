<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\SubsidyApplication;
use App\Models\SubsidyProgram;
use Illuminate\Http\Request;

class SubsidyApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SubsidyApplication::with(['program', 'farm', 'applicant:id,name,phone']);

        if (!in_array($user->role ?? '', ['admin', 'government_officer'])) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('subsidy_program_id')) {
            $query->where('subsidy_program_id', $request->subsidy_program_id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subsidy_program_id' => 'required|exists:subsidy_programs,id',
            'farm_id' => 'nullable|exists:farms,id',
            'requested_units' => 'required|integer|min:1',
            'purpose' => 'nullable|string|max:1000',
        ]);

        $program = SubsidyProgram::open()->findOrFail($validated['subsidy_program_id']);

        if ($program->max_units_per_farmer && $validated['requested_units'] > $program->max_units_per_farmer) {
            return response()->json([
                'message' => "Maximum units allowed per farmer is {$program->max_units_per_farmer}.",
            ], 422);
        }

        if (!empty($validated['farm_id'])) {
            Farm::where('id', $validated['farm_id'])
                ->where('owner_id', auth()->id())
                ->firstOrFail();
        }

        // Prevent duplicate open applications for same program
        $existing = SubsidyApplication::where('user_id', auth()->id())
            ->where('subsidy_program_id', $program->id)
            ->whereNotIn('status', ['rejected', 'disbursed'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You already have an active application for this programme.',
            ], 422);
        }

        $value = $program->unit_value
            ? $program->unit_value * $validated['requested_units']
            : null;

        $application = SubsidyApplication::create([
            'subsidy_program_id' => $program->id,
            'user_id' => auth()->id(),
            'farm_id' => $validated['farm_id'] ?? null,
            'requested_units' => $validated['requested_units'],
            'requested_value' => $value,
            'purpose' => $validated['purpose'] ?? null,
            'status' => 'draft',
            'applicant_data' => [
                'name' => auth()->user()->name,
                'phone' => auth()->user()->phone,
                'email' => auth()->user()->email,
            ],
        ]);

        return response()->json($application->load('program'), 201);
    }

    public function show(SubsidyApplication $subsidyApplication)
    {
        $this->authorizeView($subsidyApplication);

        return response()->json(
            $subsidyApplication->load(['program', 'farm', 'applicant', 'reviewer'])
        );
    }

    public function submit(SubsidyApplication $subsidyApplication)
    {
        if ($subsidyApplication->user_id !== auth()->id()) {
            abort(403);
        }
        if ($subsidyApplication->status !== 'draft') {
            return response()->json(['message' => 'Only draft applications can be submitted.'], 422);
        }

        $subsidyApplication->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Application submitted successfully.',
            'data' => $subsidyApplication->fresh(),
        ]);
    }

    public function review(Request $request, SubsidyApplication $subsidyApplication)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'status' => 'required|in:under_review,approved,rejected',
            'review_notes' => 'nullable|string|max:2000',
        ]);

        $subsidyApplication->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Application reviewed.',
            'data' => $subsidyApplication->fresh(),
        ]);
    }

    public function disburse(SubsidyApplication $subsidyApplication)
    {
        $this->authorizeGovernment();

        if ($subsidyApplication->status !== 'approved') {
            return response()->json(['message' => 'Only approved applications can be disbursed.'], 422);
        }

        $subsidyApplication->update([
            'status' => 'disbursed',
            'disbursed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subsidy marked as disbursed.',
            'data' => $subsidyApplication->fresh(),
        ]);
    }

    protected function authorizeView(SubsidyApplication $app): void
    {
        $user = auth()->user();
        if ($app->user_id !== $user->id && !in_array($user->role ?? '', ['admin', 'government_officer'])) {
            abort(403);
        }
    }

    protected function authorizeGovernment(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'government_officer'])) {
            abort(403);
        }
    }
}
