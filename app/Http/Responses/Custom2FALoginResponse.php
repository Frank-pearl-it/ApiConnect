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
        
        return response()->json([
            'token' => $user->createToken('bearerToken')->plainTextToken,
            'user' => $user
        ], 200);
    }
}