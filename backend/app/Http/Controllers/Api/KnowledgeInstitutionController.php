<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResearchInstitution;
use App\Models\ResearchPublication;
use App\Models\TrainingCourse;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class KnowledgeInstitutionController extends Controller
{
    public function mine(Request $request)
    {
        $inst = ResearchInstitution::where('owner_user_id', $request->user()->id)->first();

        return response()->json(['data' => $inst]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!RoleAccess::canManageKnowledge($user) && !RoleAccess::isAdmin($user)) {
            return response()->json([
                'message' => 'Educator or admin role required. Your role: '.$user->role,
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'institution_type' => 'nullable|in:university,research,government,ngo,private',
            'description' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:40',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'address' => 'nullable|string',
        ]);

        $existing = ResearchInstitution::where('owner_user_id', $user->id)->first();
        if ($existing) {
            $existing->update($data);
            return response()->json(['message' => 'Institution updated', 'data' => $existing->fresh()]);
        }

        $data['owner_user_id'] = $user->id;
        $data['institution_type'] = $data['institution_type'] ?? 'university';
        $data['verified'] = RoleAccess::isAdmin($user);

        $inst = ResearchInstitution::create($data);

        return response()->json(['message' => 'Institution registered', 'data' => $inst], 201);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $inst = ResearchInstitution::where('owner_user_id', $user->id)->first();

        $courses = TrainingCourse::query()
            ->when($inst, fn ($q) => $q->where('research_institution_id', $inst->id))
            ->when(!$inst, fn ($q) => $q->where('created_by', $user->id))
            ->latest()
            ->limit(30)
            ->get();

        $pubs = ResearchPublication::query()
            ->when($inst, fn ($q) => $q->where('research_institution_id', $inst->id))
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'institution' => $inst,
            'stats' => [
                'courses' => $courses->count(),
                'published_courses' => $courses->where('is_published', true)->count(),
                'publications' => $pubs->count(),
            ],
            'courses' => $courses,
            'publications' => $pubs,
        ]);
    }
}
