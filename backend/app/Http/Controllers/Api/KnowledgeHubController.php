<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemonstrationFarm;
use App\Models\ExtensionOfficer;
use App\Models\ResearchInstitution;
use App\Models\ResearchPublication;
use App\Models\TrainingCourse;
use Illuminate\Http\Request;

class KnowledgeHubController extends Controller
{
    /**
     * Unified Knowledge Hub dashboard / landing data.
     */
    public function index(Request $request)
    {
        // Latest published courses for hub (featured first)
        $featuredCourses = TrainingCourse::query()
            ->where('is_published', true)
            ->with('institution:id,name')
            ->orderByDesc('featured')
            ->latest()
            ->limit(8)
            ->get();

        $latestPublications = ResearchPublication::with(['institution:id,name', 'researcher.user:id,name'])
            ->where('is_published', true)
            ->latest('publication_date')
            ->limit(6)
            ->get();

        $extensionOfficers = ExtensionOfficer::with('user:id,name')
            ->where('verified', true)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $demoFarms = DemonstrationFarm::with('institution:id,name')
            ->latest()
            ->limit(4)
            ->get();

        $institutions = ResearchInstitution::withCount(['researchers', 'publications'])
            ->where('is_verified', true)
            ->limit(8)
            ->get();

        return response()->json([
            'featured_courses' => $featuredCourses,
            'latest_publications' => $latestPublications,
            'extension_officers' => $extensionOfficers,
            'demonstration_farms' => $demoFarms,
            'research_institutions' => $institutions,
            'categories' => [
                'crop_production',
                'livestock',
                'soil_health',
                'pest_disease',
                'climate',
                'finance',
                'marketing',
                'digital_agriculture',
            ],
        ]);
    }

    /**
     * Simple search across knowledge content.
     */
    public function search(Request $request)
    {
        $q = $request->validate(['q' => 'required|string|min:2|max:100'])['q'];

        $courses = TrainingCourse::query()->where('is_published', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'title', 'slug', 'category', 'level']);

        $publications = ResearchPublication::where('is_published', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('abstract', 'like', "%{$q}%")
                      ->orWhere('category', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'title', 'category', 'publication_date']);

        $officers = ExtensionOfficer::where('verified', true)
            ->where(function ($query) use ($q) {
                $query->where('profession', 'like', "%{$q}%")
                      ->orWhere('specialization', 'like', "%{$q}%")
                      ->orWhere('region', 'like', "%{$q}%");
            })
            ->with('user:id,name')
            ->limit(8)
            ->get();

        return response()->json([
            'query' => $q,
            'courses' => $courses,
            'publications' => $publications,
            'extension_officers' => $officers,
        ]);
    }
}
