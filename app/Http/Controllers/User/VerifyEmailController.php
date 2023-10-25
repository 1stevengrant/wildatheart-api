<?php

namespace App\Http\Controllers\User;

use App\Actions\Campfire\ApproveCampfire;
use App\Actions\EmailVerify\GetByToken;
use App\Actions\User\ConfirmEmail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        $campfire = $token->campfire;

        ConfirmEmail::run(
            user: $token->user,
            token: $token
        );

        ApproveCampfire::run(campfire: $campfire);

        auth()->login($token->user);

        return to_route('gift', ['campfire' => $token->campfire]);
    }
}
