<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Actions\User\ConfirmEmail;
use App\Actions\Event\ApproveEvent;
use App\Http\Controllers\Controller;
use App\Actions\EmailVerify\GetByToken;

class VerifyEmailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $token)
    {
        $token = GetByToken::make()->handle(token: $token);

        if (! $token) {
            abort(403, 'That token is invalid or has already been used.');
        }

        $event = $token->event;

        ConfirmEmail::run(
            user: $token->user,
            token: $token
        );

        ApproveEvent::run(event: $event);

        auth()->login($token->user);

        return to_route('gift.index', ['event' => $token->event]);
    }
}
