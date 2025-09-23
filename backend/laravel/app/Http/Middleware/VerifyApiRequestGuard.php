<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiRequestGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1) Origin/Referer 検証（Originを優先、なければRefererから scheme://host[:port] を再構成）
        $allowed = array_filter(array_map('trim', explode(',', (string) env('ALLOWED_ORIGINS', ''))));
        if (!empty($allowed)) {
            $origin = (string) $request->headers->get('Origin');
            $candidate = '';
            if ($origin !== '') {
                $candidate = $origin; // 完全一致（ポート含む）
            } else {
                $referer = (string) $request->headers->get('Referer');
                if ($referer !== '') {
                    $scheme = (string) parse_url($referer, PHP_URL_SCHEME);
                    $host = (string) parse_url($referer, PHP_URL_HOST);
                    $port = parse_url($referer, PHP_URL_PORT);
                    if ($scheme && $host) {
                        $candidate = $scheme . '://' . $host . ($port ? (':' . $port) : '');
                    }
                }
            }
            if ($candidate === '' || !in_array($candidate, $allowed, true)) {
                return response()->json(['code' => 'forbidden_origin', 'message' => 'Origin not allowed'], 403);
            }
        }

        // 2) X-Requested-With 検証
        $xrw = (string) $request->headers->get('X-Requested-With');
        if (strcasecmp($xrw, 'XMLHttpRequest') !== 0) {
            return response()->json(['code' => 'forbidden_request', 'message' => 'Invalid request header'], 403);
        }

        // 3) Double Submit Cookie (refresh/logout のみ任意適用)
        if (in_array($request->route()?->getName(), ['api.v1.auth.refresh', 'api.v1.auth.logout'], true) && (bool) env('ENABLE_REFRESH_CSRF', false)) {
            $cookie = (string) $request->cookies->get('refresh_csrf');
            $header = (string) $request->headers->get('X-CSRF-Token');
            if (!$cookie || !$header || !hash_equals($cookie, $header)) {
                return response()->json(['code' => 'forbidden_csrf', 'message' => 'CSRF token mismatch'], 403);
            }
        }

        return $next($request);
    }
}
