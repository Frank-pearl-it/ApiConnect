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

        // 1️⃣ Basic auth check
        if (!$user || !$user->role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $role = $user->role;

        // 2️⃣ Ensure user always has a company
        if (!$user->idCompany) {
            return response()->json(['message' => 'User missing company assignment'], 403);
        }

        // 3️⃣ Check if role has permission
        $permissions = $role->permissions ?? [];

        if (!in_array($requiredPermission, $permissions, true)) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        // ✅ Everything okay
        return $next($request);
    }
}
