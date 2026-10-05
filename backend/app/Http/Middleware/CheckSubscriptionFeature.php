<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionFeature
{
    public function handle(Request $request, Closure $next, string $feature = '')
    {
        // Feature flags deferred — subscription.active is the gate for now
        return $next($request);
    }
}
