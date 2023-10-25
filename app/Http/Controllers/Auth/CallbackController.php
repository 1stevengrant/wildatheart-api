<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth0\SDK\Auth0 as Auth0SDK;
use Auth0\SDK\Exception\StateException;
use Illuminate\Http\Request;
use Str;

class CallbackController extends Controller
{
    protected Auth0SDK $auth0;

    public function __construct(
        Auth0SDK $auth0
    ) {
        $this->auth0 = $auth0;
    }

    public function __invoke(Request $request)
    {
        try {
            $response = $this->auth0->exchange();

            if (! $response) {
                activity()
                    ->log('Unauthorized access attempt');
                abort(401, 'Unauthorized');
            }

            $auth0User = $this->auth0->getUser();

            $user = User::firstOrCreate(['email' => $auth0User['email']],
                [
                    'given_name' => $auth0User['given_name'] ?? null,
                    'family_name' => $auth0User['family_name'] ?? null,
                    'password' => bcrypt(Str::random(32)),
                    'email' => $auth0User['email'],
                    'provider_id' => $auth0User['sub'],
                ]);

            auth()->login($user);

            activity()
                ->withProperties([
                    'auth0User' => $auth0User,
                    'user' => $user,
                ])
                ->log('CallbackController');

            return to_route('home')->with(['notifications' => [
                'message' => 'You are now logged in!',
                'type' => 'success',
            ],
            ]);

        } catch (StateException $e) {
            activity()
                ->withProperties([
                    'exception' => $e,
                ])
                ->log('Unauthorized access attempt');

            return redirect()->route('login');
        }

    }
}
