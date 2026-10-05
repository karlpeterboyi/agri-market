<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Services\Analytics\PlatformAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected PlatformAnalyticsService $analytics
    ) {}

    /**
     * Platform-wide BI overview (admin / government).
     */
    public function platformOverview()
    {
        $this->authorizeAnalyst();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'overview' => $this->analytics->platformOverview(),
        ]);
    }

    /**
     * Marketplace BI report.
     */
    public function marketplace(Request $request)
    {
        $this->authorizeAnalyst();

        $from = $request->input('from');
        $to = $request->input('to');

        return response()->json(
            $this->analytics->marketplaceReport($from, $to)
        );
    }

    /**
     * Farm production & cost report.
     * Farmers see only their farms; admins can pass farm_id or user_id.
     */
    public function farmProduction(Request $request)
    {
        $user = auth()->user();
        $farmId = $request->input('farm_id');
        $userId = null;

        if (in_array($user->role ?? '', ['admin', 'government_officer'])) {
            $userId = $request->input('user_id');
        } else {
            // Farmer – restrict to own farms
            if ($farmId) {
                Farm::where('id', $farmId)
                    ->where('owner_id', $user->id)
                    ->firstOrFail();
            } else {
                $userId = $user->id;
            }
        }

        return response()->json(
            $this->analytics->farmProductionReport(
                $farmId ? (int) $farmId : null,
                $userId ? (int) $userId : null
            )
        );
    }

    /**
     * Simple forecast for an official indicator.
     */
    public function forecast(Request $request)
    {
        $validated = $request->validate([
            'indicator_code' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'years_ahead' => 'nullable|integer|min:1|max:10',
        ]);

        return response()->json(
            $this->analytics->forecastIndicator(
                $validated['indicator_code'],
                $validated['region'] ?? null,
                $validated['years_ahead'] ?? 2
            )
        );
    }

    /**
     * Carbon / ESG snapshot.
     */
    public function esg(Request $request)
    {
        $user = auth()->user();
        $farmId = $request->input('farm_id');

        if ($farmId && !in_array($user->role ?? '', ['admin', 'government_officer'])) {
            Farm::where('id', $farmId)
                ->where('owner_id', $user->id)
                ->firstOrFail();
        }

        if (!$farmId && !in_array($user->role ?? '', ['admin', 'government_officer'])) {
            // Aggregate only farmer's own farms
            $farmIds = Farm::where('owner_id', $user->id)->pluck('id');
            if ($farmIds->count() === 1) {
                $farmId = $farmIds->first();
            }
        }

        return response()->json(
            $this->analytics->esgSnapshot($farmId ? (int) $farmId : null)
        );
    }

    /**
     * Farmer personal dashboard (production + costs + ESG proxy).
     */
    public function farmerDashboard()
    {
        $userId = auth()->id();

        return response()->json([
            'production' => $this->analytics->farmProductionReport(null, $userId),
            'esg' => $this->analytics->esgSnapshot(null), // will be limited by ownership in future refinement
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Unified reports index – points clients to available report endpoints.
     */
    public function index()
    {
        return response()->json([
            'available_reports' => [
                [
                    'key' => 'platform_overview',
                    'name' => 'Platform BI Overview',
                    'endpoint' => '/api/analytics/platform',
                    'roles' => ['admin', 'government_officer'],
                ],
                [
                    'key' => 'marketplace',
                    'name' => 'Marketplace Performance',
                    'endpoint' => '/api/analytics/marketplace',
                    'roles' => ['admin', 'government_officer'],
                ],
                [
                    'key' => 'farm_production',
                    'name' => 'Farm Production & Costs',
                    'endpoint' => '/api/analytics/farm-production',
                    'roles' => ['farmer', 'admin', 'government_officer'],
                ],
                [
                    'key' => 'forecast',
                    'name' => 'Indicator Forecast',
                    'endpoint' => '/api/analytics/forecast',
                    'roles' => ['admin', 'government_officer', 'researcher'],
                ],
                [
                    'key' => 'esg',
                    'name' => 'Carbon / ESG Snapshot',
                    'endpoint' => '/api/analytics/esg',
                    'roles' => ['farmer', 'admin', 'government_officer'],
                ],
                [
                    'key' => 'farmer_dashboard',
                    'name' => 'Farmer Personal Dashboard',
                    'endpoint' => '/api/analytics/farmer-dashboard',
                    'roles' => ['farmer'],
                ],
                [
                    'key' => 'finance_reports',
                    'name' => 'Finance Portfolio Reports',
                    'endpoint' => '/api/finance/reports',
                    'roles' => ['admin', 'financial_institution'],
                ],
                [
                    'key' => 'admin_reports',
                    'name' => 'Admin Summary Reports',
                    'endpoint' => '/api/admin/reports',
                    'roles' => ['admin'],
                ],
                [
                    'key' => 'government_dashboard',
                    'name' => 'Government Dashboard',
                    'endpoint' => '/api/government/dashboard',
                    'roles' => ['admin', 'government_officer'],
                ],
            ],
        ]);
    }

    protected function authorizeAnalyst(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'government_officer', 'researcher'])) {
            abort(403, 'Insufficient permissions for platform analytics.');
        }
    }
}
