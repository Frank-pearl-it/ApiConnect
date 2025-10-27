<?php

namespace app\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorResponseContract;

class Custom2FALoginResponse implements TwoFactorResponseContract
{

    public function toResponse($request)
    {
        return response()->json(
            [
                'token' => Auth::user()->createToken('bearerToken')->plainTextToken,
                'user' => Auth::user()
            ],
            200
        );
    }
}
