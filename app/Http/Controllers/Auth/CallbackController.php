<?php

namespace App\Http\Controllers\Auth;

use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0 as Auth0SDK;
use App\Http\Controllers\Controller;
use Auth0\SDK\Exception\StateException;
use Illuminate\Support\Facades\Session;
use App\Services\Auth0\OAuthTokenService;
use App\Actions\User\FindCreateUpdateUser;
use App\Services\Auth0\GetUserProfileService;

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

            $oauthToken = OAuthTokenService::run();
            $auth0UserProfile = GetUserProfileService::run(
                accessToken: $oauthToken['access_token'],
                userId: $auth0User['sub']
            );
            $phone = $auth0UserProfile['user_metadata']['unverified_phone_number'] ?? null;
            $auth0User['phone'] = $phone;

            $user = FindCreateUpdateUser::run($auth0User);

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
}
