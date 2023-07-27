<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth0\SDK\Auth0 as Auth0SDK;
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

        $response = $this->auth0->exchange();

        if (! $response) {
            abort(401, 'Unauthorized');
        }

        $auth0User = $this->auth0->getUser();

        $user = User::firstOrCreate(['email' => $auth0User['email']],
            [
                'given_name' => $auth0User['given_name'],
                'family_name' => $auth0User['family_name'],
                'password' => bcrypt(Str::random(32)),
                'email' => $auth0User['email'],
                'provider_id' => $auth0User['sub'],
            ]);

        auth()->login($user);

        $redirectUrl = session()->pull('currentUrl');

        return redirect()->to($redirectUrl ? $redirectUrl : '/');
    }
}
