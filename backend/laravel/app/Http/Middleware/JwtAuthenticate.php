<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->headers->get('Authorization');
        if (!$auth || !Str::startsWith($auth, 'Bearer ')) {
            return response()->json(['code' => 'token_missing', 'message' => 'Authorization token is missing'], 401);
        }

        $token = Str::after($auth, 'Bearer ');

        try {
            $publicKeyPath = env('JWT_PUBLIC_KEY_PATH');
            if (!$publicKeyPath || !is_readable($publicKeyPath)) {
                Log::error('JWT public key not readable');
                return response()->json(['code' => 'server_misconfig', 'message' => 'Server configuration error'], 500);
            }
            $publicKey = file_get_contents($publicKeyPath);

            $now = time();
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            if (isset($decoded->nbf) && $decoded->nbf > $now + 60) {
                return response()->json(['code' => 'token_not_yet_valid', 'message' => 'Token not yet valid'], 401);
            }
            if (isset($decoded->exp) && $decoded->exp < $now - 60) {
                return response()->json(['code' => 'token_expired', 'message' => 'Token expired'], 401);
            }

            if (!isset($decoded->sub)) {
                return response()->json(['code' => 'token_invalid', 'message' => 'Invalid token'], 401);
            }
            $request->attributes->set('jwt_sub', $decoded->sub);
            $request->attributes->set('jwt', $decoded);
        } catch (\Throwable $e) {
            Log::warning('JWT decode failed: '.$e->getMessage());
            return response()->json(['code' => 'token_invalid', 'message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
