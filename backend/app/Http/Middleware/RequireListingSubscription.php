<?php

namespace App\Http\Middleware;

use App\Models\UserSubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Farmers list for free. All other commercial roles need an active subscription
 * to create marketplace listings / trucks / services / inputs.
 */
class RequireListingSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $role = strtolower((string) $user->role);
        if (in_array($role, ['farmer', 'admin'], true)) {
            return $next($request);
        }

        if (!Schema::hasTable('user_subscriptions')) {
            return response()->json([
                'message' => 'Subscriptions unavailable. Contact admin.',
                'code' => 'subscription_unavailable',
            ], 503);
        }

        $active = UserSubscription::where('user_id', $user->id)
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->where(function ($q) {
                if (Schema::hasColumn('user_subscriptions', 'expires_at')) {
                    $q->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', now()->toDateString());
                }
            })
            ->exists();

        if (!$active) {
            return response()->json([
                'message' => 'An active subscription is required to list products or services. Farmers are exempt.',
                'code' => 'subscription_required',
                'subscribe_url' => '/finance/subscriptions',
            ], 402);
        }

        return $next($request);
    }
}
