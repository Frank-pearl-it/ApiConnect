<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RestrictIntegrationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $referer = $request->headers->get('Referer');

        // Whitelisted external systems (from config/cors.php)
        $allowedOrigins = collect(config('cors.allowed_origins'))->filter();

        // Frontend URLs (must NOT be allowed here)
        $frontendUrl = config('app.frontend_url');

        // 🧱 1. Block requests from the frontend
        if (
            ($origin && str_starts_with($origin, $frontendUrl)) ||
            ($referer && str_starts_with($referer, $frontendUrl))
        ) {
            Log::warning('❌ Frontend tried to access integration route', [
                'ip' => $request->ip(),
                'origin' => $origin,
                'referer' => $referer,
                'path' => $request->path(),
            ]);

            return response()->json([
                'message' => 'Access from the frontend is not allowed for integration routes.',
            ], 403);
        }

        // 🧱 2. Block requests with browser session cookies (XSRF or Laravel session)
        if ($request->hasCookie('XSRF-TOKEN') || $request->hasCookie('laravel_session')) {
            Log::warning('❌ Browser cookies detected on integration route', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'message' => 'Browser-based requests are not allowed for integration routes.',
            ], 403);
        }

        // 🧱 3. Block requests without any Origin (Postman, curl) — unless testing mode is on
        if (!$origin) {
            if (config('app.allow_postman_testing', false)) {
                Log::info('⚠️ Postman access allowed temporarily', [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                ]);
            } else {
                Log::warning('❌ Request without Origin blocked (likely Postman or curl)', [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                ]);

                return response()->json([
                    'message' => 'Requests without a valid Origin are not allowed.',
                ], 403);
            }
        }

        // ✅ 4. Allow if Origin is explicitly whitelisted
        if ($origin && !$allowedOrigins->contains($origin)) {
            Log::warning('❌ Unrecognized Origin attempted access', [
                'ip' => $request->ip(),
                'origin' => $origin,
                'path' => $request->path(),
            ]);

            return response()->json([
                'message' => 'This origin is not authorized to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
