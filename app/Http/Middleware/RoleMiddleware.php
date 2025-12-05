<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $requiredPermission)
    {
        $user = Auth::user();

        // Handle missing role gracefully
        if (!$user || !$user->role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $permissions = $user->role->permissions ?? [];

        // ✅ Check by key existence instead of in_array
        if (empty($permissions[$requiredPermission]) || $permissions[$requiredPermission] !== true) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        return $next($request);
    }
}
