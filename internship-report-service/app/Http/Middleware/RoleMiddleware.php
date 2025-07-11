<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $userRole = $request->get('user_role');

        if (! $userRole) {
            return response()->json(['message' => 'Unauthorized: Role not found in token'], 401);
        }

        if (! in_array($userRole, $roles)) {
            return response()->json(['message' => 'Forbidden: Unauthorized role'], 403);
        }

        return $next($request);
    }
}
