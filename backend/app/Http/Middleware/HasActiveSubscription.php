<?php

namespace App\Http\Middleware;

use App\Models\UserSubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HasActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (in_array(strtolower((string) $user->role), ['farmer', 'admin'], true)) {
            return $next($request);
        }

        if (!Schema::hasTable('user_subscriptions')) {
            return $next($request);
        }

        $ok = UserSubscription::where('user_id', $user->id)
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->where(function ($q) {
                if (Schema::hasColumn('user_subscriptions', 'expires_at')) {
                    $q->whereNull('expires_at')
                        ->orWhereDate('expires_at', '>=', now()->toDateString());
                }
            })
            ->exists();

        if (!$ok) {
            return response()->json([
                'message' => 'Active subscription required.',
                'code' => 'subscription_required',
                'subscribe_url' => '/finance/subscriptions',
            ], 402);
        }

        return $next($request);
    }
}
