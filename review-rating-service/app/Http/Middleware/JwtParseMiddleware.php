<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtParseMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthorized. Token missing.'], 401);
        }

        try {
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        } catch (Exception $e) {
            return response()->json(['message' => 'Unauthorized. Token invalid.'], 401);
        }

        $request->attributes->set('jwt', (array) $payload);
        $request->attributes->set('user_id', $payload->sub);
        return $next($request);
    }
}
