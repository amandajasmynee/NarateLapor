<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class JwtParseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            $request->merge([
                'user_id' => $payload['sub'],
                'user_email' => $payload['email'] ?? null,
                'user_role' => $payload['role'] ?? null,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unauthorized. Token invalid or missing.'], 401);
        }

        return $next($request);
    }
}
