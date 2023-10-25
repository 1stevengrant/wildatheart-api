<?php

namespace App\Http\Controllers\Auth;

use App\Actions\EmailVerify\GetByToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OneTimeLoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token)
    {
        $emailVerify = GetByToken::run($token);

        if (! $token) {
            activity()
                ->log('Token Not Found');
            abort(404, 'Token not found');
        }

        if (! $emailVerify) {
            activity()
                ->log('No email verify found');
            abort(404, 'Token not found');
        }

        if ($emailVerify->expires_at->isPast()) {
            $emailVerify->delete();
            activity()
                ->log('Token expired and deleted');
            abort(404, 'Token not found');
        }

        $user = $emailVerify->user;

        if (! $user->email_verified_at) {
            $user->update([
                'email_verified_at' => now(),
            ]);
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->log('User email verified');
        }

        auth()->login($user);

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User logged in');

        $emailVerify->delete();

        activity()
            ->causedBy($user)
            ->performedOn($emailVerify)
            ->log('Email verify deleted');

        return to_route('home')->with(['notifications' => [
            'message' => 'You are logged in',
            'type' => 'success',
        ],
        ]);
    }
}
