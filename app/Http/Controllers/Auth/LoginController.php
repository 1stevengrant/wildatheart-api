<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth0\SDK\Auth0 as Auth0SDK;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        activity()
            ->log('Clearing Auth0 Session');

        activity()
            ->log('Redirecting to Auth0 Login');

        return Inertia::location($this->auth0->login());
    }
}
