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
        
        return response()->json([
            'token' => $user->createToken('bearerToken')->plainTextToken,
            'user' => $user
        ], 200);
    }
}