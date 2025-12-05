<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendIntegrity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $origin = $request->headers->get('Origin');
        $frontendUrl = config('app.frontend_url');

        // 1️⃣ Check authentication
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2️⃣ Validate origin
        if ($origin && !str_starts_with($origin, $frontendUrl)) {
            Log::warning('Blocked non-frontend origin on frontend route', [
                'origin' => $origin,
                'expected' => $frontendUrl,
                'user_id' => $user->id,
            ]);
            return response()->json(['message' => 'Invalid origin'], 403);
        }

        // 3️⃣ Reload user + role from DB to ensure roles/permissions are up to date
        $freshUser = $user->fresh(['role']);
        if (!$freshUser || !$freshUser->role) {
            Log::warning('Missing or invalid role detected', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return response()->json(['message' => 'Invalid role'], 403);
        }

        // 4️⃣ Ensure idCompany consistency between user and role
        if ($freshUser->idCompany !== $freshUser->role->idCompany) {
            Log::error('Company mismatch detected between user and role', [
                'user_id' => $user->id,
                'user_company' => $freshUser->idCompany,
                'role_id' => $freshUser->role->id,
                'role_company' => $freshUser->role->idCompany,
            ]);
            return response()->json(['message' => 'Company mismatch detected'], 403);
        }

        // 5️⃣ Compare permission sets (detects stale roles or tampering)
        if ($freshUser->role->permissions !== $user->role->permissions) {
            Log::warning('Role/permission mismatch detected', [
                'user_id' => $user->id,
                'old_role' => $user->role->name ?? null,
                'new_role' => $freshUser->role->name ?? null,
            ]);
            return response()->json(['message' => 'Permission mismatch'], 403);
        }

        // 6️⃣ Verify Sanctum token still active
        $token = $user->currentAccessToken();
        if (!$token) {
            return response()->json(['message' => 'Token missing or invalid'], 401);
        }

        // 7️⃣ Optional fingerprint validation
        $strict = env('FRONTEND_STRICT_FINGERPRINT', false);
        $storedFingerprint = $token->abilities['fingerprint'] ?? null;
        $currentFingerprint = hash('sha256', $request->userAgent() . substr($request->ip(), 0, 7));

        if ($storedFingerprint && $storedFingerprint !== $currentFingerprint) {
            Log::warning('Fingerprint mismatch detected', [
                'user_id' => $user->id,
                'old' => $storedFingerprint,
                'new' => $currentFingerprint,
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);

            if ($strict) {
                return response()->json(['message' => 'Session fingerprint mismatch'], 401);
            }
        }

        // 8️⃣ Optional HTTPS check (only enforce in production)
        if (app()->environment('production') && filter_var(env('FRONTEND_REQUIRE_HTTPS', true), FILTER_VALIDATE_BOOLEAN)) {
            if (!$request->isSecure()) {
                Log::warning('Non-HTTPS request blocked on frontend route', [
                    'ip' => $request->ip(),
                    'user_id' => $user->id,
                    'scheme' => $request->getScheme(),
                    'forwarded_proto' => $request->header('X-Forwarded-Proto'),
                ]);
                return response()->json(['message' => 'Insecure connection not allowed'], 403);
            }
        }

        return $next($request);
    }
}
