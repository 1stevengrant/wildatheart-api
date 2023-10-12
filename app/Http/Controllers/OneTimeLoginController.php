<?php

namespace App\Http\Controllers;

use App\Actions\EmailVerify\GetByToken;
use Illuminate\Http\Request;

class OneTimeLoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token)
    {
        $emailVerify = GetByToken::run($token);

        if (! $token && $emailVerify->expires_at->isPast()) {
            abort(404, 'Token not found');
        }

        $user = $emailVerify->user;

        if (! $user->email_verified_at) {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        auth()->login($user);

        $emailVerify->delete();

        return to_route('home')->with(['notifications' => [
            'message' => 'You are logged in',
            'type' => 'success',
        ],
        ]);
    }
}
