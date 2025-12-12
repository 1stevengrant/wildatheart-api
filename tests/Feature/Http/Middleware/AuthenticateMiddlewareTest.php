<?php

use Auth0\SDK\Auth0;
use Illuminate\Auth\GenericUser;

use function Pest\Laravel\getJson;

use Illuminate\Support\Facades\Auth;
use Auth0\SDK\Configuration\SdkConfiguration;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Create a minimal Auth0 API instance for testing (won't actually validate tokens)
    $this->app->singleton('auth0.api', function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });
});

it('returns 401 when user is not authenticated', function () {
    $response = getJson(route('auth.me.nav'));

    $response->assertStatus(401);
});

it('allows authenticated user through middleware', function () {
    $auth0User = new GenericUser([
        'id' => 'auth0|test123',
        'sub' => 'auth0|test123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me.nav'));

    $response->assertOk();
});

it('returns json error response for unauthenticated api requests', function () {
    $response = getJson(route('auth.me'));

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});
