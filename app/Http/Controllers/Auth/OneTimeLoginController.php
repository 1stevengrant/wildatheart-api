<?php

namespace App\Http\Controllers\Auth;

use App\Actions\EmailVerify\GetByToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OneTimeLoginController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $emailVerify = GetByToken::run($token);

        abort_unless($token && $emailVerify, 404, 'Token not found');

        $user = $emailVerify->user;
        abort_unless($emailVerify->expires_at->isFuture(), 404, tap($emailVerify, function ($ev) {
            activity()->log('Token expired and deleted');
            $ev->delete();
        }));

        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->log('User email verified');
        }

        auth()->login($user);

        $emailVerify->delete();

        activity()
            ->causedBy($user)
            ->performedOn($emailVerify)
            ->log('Email verify deleted');

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('User logged in');

        return to_route('home')->with(['notifications' => [
            'message' => 'You are logged in',
            'type' => 'success',
        ]]);
    }
}
