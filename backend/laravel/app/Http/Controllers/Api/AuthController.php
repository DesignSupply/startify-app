<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ログイン
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['code' => 'invalid_credentials', 'message' => 'メールアドレスまたはパスワードが無効です'], 401);
        }

        $accessToken = $this->createAccessToken($user);
        $refreshTokenValue = $this->issueRefreshToken($user, $request);

        $domain = env('REFRESH_COOKIE_DOMAIN', $request->getHost());

        $response = response()->json(['access_token' => $accessToken])
            ->cookie('refresh_token', $refreshTokenValue, (int) env('JWT_REFRESH_TTL', 20160), '/api/v1/auth', $domain, true, true, false, 'None');

        if (env('ENABLE_REFRESH_CSRF', false)) {
            $csrf = bin2hex(random_bytes(16));
            // SameSite=None は Secure 必須
            $response->cookie('refresh_csrf', $csrf, (int) env('JWT_REFRESH_TTL', 20160), '/api/v1/auth', $domain, true, false, false, 'None');
        }

        return $response;
    }

    // リフレッシュトークン再発行
    public function refresh(Request $request)
    {
        $cookieVal = $request->cookies->get('refresh_token');
        if (!$cookieVal) {
            return response()->json(['code' => 'refresh_missing', 'message' => 'リフレッシュトークンがありません'], 401);
        }

        [$tokenId, $secret] = $this->splitRefreshValue($cookieVal);
        if (!$tokenId || !$secret) {
            return response()->json(['code' => 'refresh_invalid', 'message' => 'リフレッシュトークンが無効です'], 401);
        }

        $token = RefreshToken::query()->where('id', $tokenId)->first();
        if (!$token) {
            return response()->json(['code' => 'refresh_invalid', 'message' => 'リフレッシュトークンが無効です'], 401);
        }

        // validate hash, expiry, revoked
        $expected = hash('sha256', $cookieVal);
        $expired = $token->expires_at && $token->expires_at->isPast();
        if (!hash_equals($token->token_hash, $expected) || $token->revoked_at || $expired) {
            return response()->json(['code' => 'refresh_invalid', 'message' => 'リフレッシュトークンが無効です'], 401);
        }

        $user = User::find($token->user_id);
        if (!$user) {
            return response()->json(['code' => 'user_not_found', 'message' => 'ユーザーが見つかりません'], 404);
        }

        // rotate: revoke old and issue new
        $token->revoked_at = now();
        $token->save();

        $accessToken = $this->createAccessToken($user);
        $newRefreshValue = $this->issueRefreshToken($user, $request);

        $domain = env('REFRESH_COOKIE_DOMAIN', $request->getHost());

        $response = response()->json(['access_token' => $accessToken])
            ->cookie('refresh_token', $newRefreshValue, (int) env('JWT_REFRESH_TTL', 20160), '/api/v1/auth', $domain, true, true, false, 'None');

        if (env('ENABLE_REFRESH_CSRF', false)) {
            $csrf = bin2hex(random_bytes(16));
            // SameSite=None は Secure 必須
            $response->cookie('refresh_csrf', $csrf, (int) env('JWT_REFRESH_TTL', 20160), '/api/v1/auth', $domain, true, false, false, 'None');
        }

        return $response;
    }

    // ログアウト
    public function logout(Request $request)
    {
        $cookieVal = $request->cookies->get('refresh_token');
        if ($cookieVal) {
            [$tokenId, $secret] = $this->splitRefreshValue($cookieVal);
            if ($tokenId && $secret) {
                $expected = hash('sha256', $cookieVal);
                $token = RefreshToken::query()->where('id', $tokenId)->first();
                if ($token && hash_equals($token->token_hash, $expected) && !$token->revoked_at) {
                    $token->revoked_at = now();
                    $token->save();
                }
            }
        }

        // delete cookie
        $domain = env('REFRESH_COOKIE_DOMAIN', $request->getHost());
        $response = response()->noContent()
            ->cookie('refresh_token', '', -1, '/api/v1/auth', $domain, true, true, false, 'None');

        if (env('ENABLE_REFRESH_CSRF', false)) {
            // 削除も発行時と同一属性（Secure含む）で
            $response->cookie('refresh_csrf', '', -1, '/api/v1/auth', $domain, true, false, false, 'None');
        }

        return $response;
    }

    // JWT検証
    public function me(Request $request)
    {
        $userId = $request->attributes->get('jwt_sub');
        if (!$userId) {
            return response()->json(['code' => 'token_invalid', 'message' => 'トークンが無効です'], 401);
        }
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['code' => 'user_not_found', 'message' => 'ユーザーが見つかりません'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => optional($user->created_at)?->toIso8601String(),
        ]);
    }

    private function createAccessToken(User $user): string
    {
        $privateKeyPath = env('JWT_PRIVATE_KEY_PATH');
        $privateKey = $privateKeyPath && is_readable($privateKeyPath) ? file_get_contents($privateKeyPath) : null;
        if (!$privateKey) {
            Log::error('JWT private key not readable');
            abort(500, 'Server configuration error');
        }

        $now = time();
        $ttl = (int) env('JWT_ACCESS_TTL', 15) * 60; // seconds
        $payload = [
            'iss' => url('/'),
            'sub' => (string) $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'jti' => Str::orderedUuid()->toString(),
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    private function issueRefreshToken(User $user, Request $request): string
    {
        $raw = bin2hex(random_bytes(32));
        $minutes = (int) env('JWT_REFRESH_TTL', 20160);
        // Pre-generate UUID to compute value and hash before first insert
        $id = (string) Str::uuid();
        $value = $id . '.' . $raw;

        $data = [
            'id' => $id,
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'ua' => (string) $request->userAgent(),
            'expires_at' => now()->addMinutes($minutes),
            'token_hash' => hash('sha256', $value),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        RefreshToken::query()->create($data);

        return $value;
    }

    private function splitRefreshValue(string $value): array
    {
        $pos = strpos($value, '.');
        if ($pos === false) {
            return [null, null];
        }
        return [substr($value, 0, $pos), substr($value, $pos + 1)];
    }
}


