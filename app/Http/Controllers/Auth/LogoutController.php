<?php

namespace App\Http\Controllers\Auth;

use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0 as Auth0SDK;
use App\Http\Controllers\Controller;

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
        LogActivityJob::dispatch(
            logName: 'auth',
            description: 'user logged out',
            properties: ['user' => auth()->user()],
        );

        auth()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $route = (string) url('/');

        $url = $this->auth0->logout($route);

        return redirect()->away($url);

    }
}
