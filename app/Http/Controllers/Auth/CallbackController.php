<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0 as Auth0SDK;
use App\Http\Controllers\Controller;
use App\Jobs\CheckIfUserShouldBeAdmin;
use Auth0\SDK\Exception\StateException;
use Illuminate\Support\Facades\Session;

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
            abort_unless($response, 401, dispatch(new LogActivityJob(
                logName: 'auth',
                description: 'Unauthorized access attempt',
            )));

            $auth0User = $this->auth0->getUser();
            ray($auth0User);

            $user = $this->findOrCreateUser($auth0User);

            auth()->login($user);

            dispatch(new LogActivityJob(
                logName: 'auth',
                description: 'user logged in',
                properties: ['user' => $user, 'auth0User' => $auth0User],
            ));

            $intendedUrl = Session::get('auth.intended_url');

            return redirect()->to($intendedUrl)->with([
                'notifications' => [
                    'message' => 'You are now logged in!',
                    'type' => 'success',
                ],
            ]);

        } catch (StateException $e) {
            dispatch(new LogActivityJob(
                logName: 'auth',
                description: 'Unauthorized access attempt',
                properties: ['exception' => $e],
            ));

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
}
