<?php
// app/Http/Responses/Custom2FALoginResponse.php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorResponseContract;

class Custom2FALoginResponse implements TwoFactorResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // Load the role relationship if it exists
        if (method_exists($user, 'role')) {
            $user->load('role');
        }

        $fingerprint = hash('sha256', $request->userAgent() . substr($request->ip(), 0, 7));

        $token = $user->createToken('bearerToken', [
            'fingerprint' => $fingerprint,
        ])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }
}