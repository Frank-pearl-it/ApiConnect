<?php
// app/Http/Responses/Custom2FAResponse.php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;

class Custom2FAResponse implements TwoFactorConfirmedResponseContract
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