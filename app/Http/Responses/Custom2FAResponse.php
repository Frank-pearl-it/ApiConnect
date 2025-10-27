<?php

namespace App\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;

class Custom2FAResponse implements TwoFactorConfirmedResponseContract
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
