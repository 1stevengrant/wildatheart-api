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
            abort_unless($response, 401, $this->logAndReturn('Unauthorized access attempt'));

            $auth0User = $this->auth0->getUser();
            $user = $this->findOrCreateUser($auth0User);

            auth()->login($user);

            $this->logActivity('CallbackController', ['auth0User' => $auth0User, 'user' => $user]);

            return to_route('home')->with([
                'notifications' => [
                    'message' => 'You are now logged in!',
                    'type' => 'success',
                ],
            ]);

        } catch (StateException $e) {
            $this->logActivity('Unauthorized access attempt', ['exception' => $e]);

            return redirect()->route('login');
        }
    }

    protected function findOrCreateUser(array $auth0User): User
    {
        return User::firstOrCreate(
            ['email' => $auth0User['email']],
            [
                'given_name' => $auth0User['given_name'] ?? null,
                'family_name' => $auth0User['family_name'] ?? null,
                'password' => bcrypt(Str::random(32)),
                'provider_id' => $auth0User['sub'],
            ]
        );
    }

    protected function logActivity(string $message, array $properties = [])
    {
        activity()
            ->withProperties($properties)
            ->log($message);
    }

    protected function logAndReturn(string $message)
    {
        $this->logActivity($message);

        return $message;
    }
}
