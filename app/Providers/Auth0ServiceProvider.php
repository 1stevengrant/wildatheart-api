<?php

namespace App\Providers;

use Auth0\SDK\Auth0;
use Illuminate\Support\ServiceProvider;

class Auth0ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Auth0::class, function () {
            return new Auth0([
                'domain' => config('auth0.base_url'),
                'clientId' => config('auth0.client_id'),
                'clientSecret' => config('auth0.client_secret'),
                'cookieSecret' => config('auth0.cookie_secret'),
                'redirectUri' => config('auth0.redirect'),
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function provides(): array
    {
        return [Auth0::class];
    }
}
