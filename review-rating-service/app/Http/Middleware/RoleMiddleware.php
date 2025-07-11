<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        $jwt = $request->attributes->get('jwt');

        if (! $jwt || ($jwt['role'] ?? null) !== $role) {
            return response()->json(['message' => 'Forbidden: Unauthorized role'], 403);
        }

        return $next($request);
    }
}
