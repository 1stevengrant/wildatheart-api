<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\LogActivity;
use Auth0\SDK\Auth0 as Auth0SDK;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    protected Auth0SDK $auth0;

    public function __construct(
        Auth0SDK $auth0
    ) {
        $this->auth0 = $auth0;
    }

    public function __invoke(Request $request)
    {
        dispatch(new LogActivity(
            logName: 'auth',
            description: 'user logged out',
            properties: ['user' => auth()->user()],
        ));

        auth()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $route = (string) url('/');

        $url = $this->auth0->logout($route);

        return redirect()->away($url);

    }
}
