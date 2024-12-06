<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0 as Auth0SDK;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    protected Auth0SDK $auth0;

    public function __construct(
        Auth0SDK $auth0
    ) {
        $this->auth0 = $auth0;
    }

    public function __invoke(Request $request)
    {
        $this->auth0->clear();
        session(['url.intended' => url()->previous()]);

        dispatch(new LogActivityJob(
            logName: 'auth',
            description: 'Clearing Auth0 Session',
        ));

        dispatch(new LogActivityJob(
            logName: 'auth',
            description: 'Redirecting to Auth0 Login',
        ));

        return Inertia::location($this->auth0->login());
    }
}
