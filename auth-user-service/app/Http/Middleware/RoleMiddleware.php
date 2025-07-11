<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        try {
            $user = auth()->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized: Invalid token'], 401);
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthorized: No user found'], 401);
        }

        if (! in_array($user->role, $roles)) {
            return response()->json(['message' => 'Forbidden: Unauthorized role'], 403);
        }

        return $next($request);
    }
}
