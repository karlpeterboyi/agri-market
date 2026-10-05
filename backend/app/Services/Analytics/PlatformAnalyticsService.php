<?php

namespace App\Services\Analytics;

use App\Models\AgriculturalStatistic;
use App\Models\CropCycle;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\LoanApplication;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductListing;
use App\Models\SubsidyApplication;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsService
{
    /**
     * High-level platform KPIs for admin / BI dashboards.
     */
    public function platformOverview(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'farmers' => User::where('role', 'farmer')->count(),
                'buyers' => User::where('role', 'buyer')->count(),
                'providers' => User::where('role', 'provider')->count(),
            ],
            'farms' => [
                'total' => Farm::count(),
                'total_area_ha' => (float) Farm::sum('total_area_hectares'),
                'cultivated_area_ha' => (float) Farm::sum('cultivated_area_hectares'),
            ],
            'marketplace' => [
                'active_listings' => ProductListing::where('status', 'available')->count(),
                'total_listings' => ProductListing::count(),
                'orders' => Order::count(),
                'gmv' => (float) Payment::where('status', 'paid')->sum('amount'),
                'escrow_balance' => (float) Payment::where('status', 'paid')->sum('escrow_amount'),
            ],
            'finance' => [
                'loan_applications' => LoanApplication::count(),
                'loans_approved' => LoanApplication::whereIn('status', ['approved', 'disbursed', 'completed'])->count(),
                'wallets' => Wallet::count(),
                'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            ],
            'government' => [
                'subsidy_applications' => SubsidyApplication::count(),
                'subsidy_approved' => SubsidyApplication::where('status', 'approved')->count(),
                'subsidy_disbursed' => SubsidyApplication::where('status', 'disbursed')->count(),
            ],
        ];
    }

    /**
     * Marketplace performance by commodity / region / time.
     */
    public function marketplaceReport(?string $from = null, ?string $to = null): array
    {
        $ordersQuery = Order::query();
        $paymentsQuery = Payment::where('status', 'paid');

        if ($from) {
            $ordersQuery->whereDate('created_at', '>=', $from);
            $paymentsQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $ordersQuery->whereDate('created_at', '<=', $to);
            $paymentsQuery->whereDate('created_at', '<=', $to);
        }

        $ordersByStatus = (clone $ordersQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $gmv = (float) $paymentsQuery->sum('amount');

        $listingsByRegion = ProductListing::select('region', DB::raw('count(*) as count'))
            ->groupBy('region')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'period' => ['from' => $from, 'to' => $to],
            'orders_by_status' => $ordersByStatus,
            'gmv' => $gmv,
            'listings_by_region' => $listingsByRegion,
            'total_orders' => $ordersQuery->count(),
        ];
    }

    /**
     * Farm production & cost summary for a farmer or platform.
     */
    public function farmProductionReport(?int $farmId = null, ?int $userId = null): array
    {
        $cycles = CropCycle::with(['crop', 'farm', 'fieldBlock']);

        if ($farmId) {
            $cycles->where('farm_id', $farmId);
        } elseif ($userId) {
            $cycles->whereHas('farm', fn ($q) => $q->where('owner_id', $userId));
        }

        $cycles = $cycles->get();

        $byCrop = $cycles->groupBy(fn ($c) => $c->crop->name ?? 'Unknown')
            ->map(function ($group) {
                return [
                    'cycles' => $group->count(),
                    'area_ha' => round($group->sum('area_hectares'), 2),
                    'expected_yield' => round($group->sum('expected_yield'), 2),
                    'actual_yield' => round($group->sum('actual_yield'), 2),
                ];
            });

        $activitiesQuery = FarmActivity::query();
        if ($farmId) {
            $activitiesQuery->where('farm_id', $farmId);
        } elseif ($userId) {
            $activitiesQuery->whereHas('farm', fn ($q) => $q->where('owner_id', $userId));
        }

        $costs = $activitiesQuery->selectRaw('
            activity_type,
            COUNT(*) as count,
            COALESCE(SUM(cost), 0) as input_cost,
            COALESCE(SUM(labour_cost), 0) as labour_cost,
            COALESCE(SUM(cost + labour_cost), 0) as total_cost
        ')->groupBy('activity_type')->get();

        return [
            'crop_summary' => $byCrop,
            'cost_by_activity' => $costs,
            'total_cycles' => $cycles->count(),
            'total_area_ha' => round($cycles->sum('area_hectares'), 2),
        ];
    }

    /**
     * Simple linear trend forecast from agricultural_statistics.
     */
    public function forecastIndicator(string $indicatorCode, ?string $region = null, int $yearsAhead = 2): array
    {
        $query = AgriculturalStatistic::where('indicator_code', $indicatorCode)
            ->whereNull('month')
            ->orderBy('year');

        if ($region) {
            $query->where('region', $region);
        } else {
            $query->whereNull('region');
        }

        $history = $query->get(['year', 'value', 'unit']);

        if ($history->count() < 2) {
            return [
                'indicator_code' => $indicatorCode,
                'history' => $history,
                'forecast' => [],
                'message' => 'Insufficient historical data for forecast.',
            ];
        }

        // Simple linear regression on year vs value
        $n = $history->count();
        $sumX = $history->sum('year');
        $sumY = $history->sum('value');
        $sumXY = $history->sum(fn ($r) => $r->year * $r->value);
        $sumX2 = $history->sum(fn ($r) => $r->year * $r->year);

        $denom = ($n * $sumX2 - $sumX * $sumX);
        $slope = $denom != 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $lastYear = $history->max('year');
        $unit = $history->last()->unit;
        $forecast = [];

        for ($i = 1; $i <= $yearsAhead; $i++) {
            $year = $lastYear + $i;
            $value = round($intercept + $slope * $year, 2);
            $forecast[] = [
                'year' => $year,
                'value' => max(0, $value),
                'unit' => $unit,
                'type' => 'forecast',
            ];
        }

        return [
            'indicator_code' => $indicatorCode,
            'region' => $region,
            'history' => $history,
            'forecast' => $forecast,
            'trend_slope' => round($slope, 4),
        ];
    }

    /**
     * Basic Carbon / ESG style indicators (proxy metrics).
     */
    public function esgSnapshot(?int $farmId = null): array
    {
        $farmsQuery = Farm::query();
        if ($farmId) {
            $farmsQuery->where('id', $farmId);
        }

        $totalArea = (float) $farmsQuery->sum('total_area_hectares');
        $irrigated = (float) (clone $farmsQuery)->sum('irrigated_area_hectares');
        $cultivated = (float) (clone $farmsQuery)->sum('cultivated_area_hectares');

        // Proxy metrics – replace with real carbon calculations later
        $irrigationIntensity = $totalArea > 0 ? round($irrigated / $totalArea, 3) : 0;
        $landUseIntensity = $totalArea > 0 ? round($cultivated / $totalArea, 3) : 0;

        // Very rough carbon sequestration proxy (tCO2e) using cultivated area
        $estimatedSequestration = round($cultivated * 1.5, 2); // placeholder factor

        return [
            'total_area_ha' => $totalArea,
            'cultivated_area_ha' => $cultivated,
            'irrigated_area_ha' => $irrigated,
            'irrigation_intensity' => $irrigationIntensity,
            'land_use_intensity' => $landUseIntensity,
            'estimated_carbon_sequestration_tco2e' => $estimatedSequestration,
            'notes' => 'Carbon figures are proxy estimates. Integrate farm-level practices & emission factors for production-grade ESG reporting.',
        ];
    }
}
