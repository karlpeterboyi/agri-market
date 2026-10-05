<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks the users.role column (not Spatie roles).
 * Usage: middleware('user.role:admin') or user.role:admin,provider
 */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Support comma-separated roles in a single parameter
        $allowed = [];
        foreach ($roles as $roleArg) {
            foreach (explode(',', $roleArg) as $r) {
                $r = trim($r);
                if ($r !== '') {
                    $allowed[] = $r;
                }
            }
        }

        if ($allowed && !in_array($user->role, $allowed, true)) {
            return response()->json([
                'message' => 'Forbidden. Required role: '.implode('|', $allowed),
                'your_role' => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
