<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InputListing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductListing;
use App\Models\Service;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    protected function ensureAdmin(): void
    {
        $role = auth()->user()->role ?? '';
        if ($role !== 'admin') {
            abort(403, 'Admin only. Your role: '.$role);
        }
    }

    public function dashboard(): JsonResponse
    {
        $this->ensureAdmin();

        $usersByRole = User::query()
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'users' => User::count(),
            'users_by_role' => $usersByRole,
            'farmers' => User::where('role', 'farmer')->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'providers' => User::where('role', 'provider')->count(),
            'agrodealers' => User::where('role', 'agrodealer')->count(),
            'processors' => User::where('role', 'processor')->count(),
            'transporters' => User::where('role', 'transporter')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'listings' => ProductListing::count(),
            'active_listings' => ProductListing::where('status', 'available')->count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'payments' => Payment::count(),
            'total_sales' => (float) Payment::where('status', 'paid')->sum('amount'),
            'escrow_balance' => (float) Payment::where('status', 'paid')->sum('escrow_amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'withdrawals' => Withdrawal::count(),
        ];

        if (Schema::hasTable('input_listings')) {
            $payload['input_listings'] = InputListing::count();
            $payload['active_input_listings'] = InputListing::where('status', 'available')->count();
        }
        if (Schema::hasTable('services')) {
            $payload['services'] = Service::count();
        }
        if (class_exists(\App\Models\MachineryListing::class) && Schema::hasTable('machinery_listings')) {
            $payload['machinery_listings'] = \App\Models\MachineryListing::count();
        }

        return response()->json($payload);
    }

    public function users(): JsonResponse
    {
        $this->ensureAdmin();
        return response()->json(User::latest()->get());
    }

    public function listings(): JsonResponse
    {
        $this->ensureAdmin();
        return response()->json(
            ProductListing::with(['commodity', 'seller'])->latest()->get()
        );
    }

    public function orders(): JsonResponse
    {
        $this->ensureAdmin();
        return response()->json(
            Order::with(['buyer', 'seller', 'listing.commodity'])->latest()->get()
        );
    }

    public function payments()
    {
        $this->ensureAdmin();
        return response()->json(
            Payment::with(['payer', 'order.buyer', 'order.seller'])->latest()->get()
        );
    }

    public function transporters()
    {
        $this->ensureAdmin();
        return response()->json(
            User::where('role', 'transporter')->latest()->get()
        );
    }
}
