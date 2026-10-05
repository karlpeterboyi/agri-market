<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalRegistration;
use App\Models\AgriculturalStatistic;
use App\Models\GovernmentAnnouncement;
use App\Models\SubsidyApplication;
use App\Models\SubsidyProgram;
use Illuminate\Http\Request;

class GovernmentDashboardController extends Controller
{
    /**
     * High-level government / institutional dashboard.
     */
    public function index(Request $request)
    {
        $openPrograms = SubsidyProgram::open()->count();
        $pendingApplications = SubsidyApplication::where('status', 'submitted')->count();
        $approvedApplications = SubsidyApplication::where('status', 'approved')->count();
        $pendingRegistrations = AgriculturalRegistration::where('status', 'pending')->count();
        $activeAnnouncements = GovernmentAnnouncement::published()->count();

        $recentAnnouncements = GovernmentAnnouncement::published()
            ->latest('published_at')
            ->limit(5)
            ->get(['id', 'title', 'category', 'priority', 'published_at', 'source_organisation']);

        $recentApplications = SubsidyApplication::with('program:id,name', 'applicant:id,name')
            ->latest()
            ->limit(8)
            ->get();

        $latestStats = AgriculturalStatistic::orderByDesc('year')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json([
            'summary' => [
                'open_subsidy_programs' => $openPrograms,
                'pending_subsidy_applications' => $pendingApplications,
                'approved_subsidy_applications' => $approvedApplications,
                'pending_registrations' => $pendingRegistrations,
                'active_announcements' => $activeAnnouncements,
            ],
            'recent_announcements' => $recentAnnouncements,
            'recent_subsidy_applications' => $recentApplications,
            'latest_statistics' => $latestStats,
        ]);
    }
}
