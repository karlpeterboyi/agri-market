<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductListing;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Analytics\PlatformAnalyticsService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index()
    {
        return response()->json([
            'users' => User::count(),
            'farmers' => User::where('role', 'farmer')->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'listings' => ProductListing::count(),
            'orders' => Order::count(),
            'payments' => Payment::count(),
            'withdrawals' => Withdrawal::count(),
            'total_sales' => Payment::where('status', 'paid')->sum('amount'),
            'escrow_balance' => Payment::where('status', 'paid')->sum('escrow_amount'),
        ]);
    }

    /**
     * Richer analytics endpoint (used by subscription feature gate in routes).
     */
    public function analytics(Request $request, PlatformAnalyticsService $analytics)
    {
        return response()->json([
            'overview' => $analytics->platformOverview(),
            'marketplace' => $analytics->marketplaceReport(
                $request->input('from'),
                $request->input('to')
            ),
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
