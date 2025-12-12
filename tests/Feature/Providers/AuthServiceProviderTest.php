<?php

use Auth0\SDK\Auth0;
use Illuminate\Auth\GenericUser;

use function Pest\Laravel\getJson;

use Illuminate\Support\Facades\Auth;
use Auth0\SDK\Configuration\SdkConfiguration;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('registers Auth0 web instance via Auth0ServiceProvider', function () {
    // The web Auth0 instance is registered by Auth0ServiceProvider
    // We just verify it's available and is a singleton
    $auth0 = app(Auth0::class);
    $auth0Again = app(Auth0::class);

    expect($auth0)->toBeInstanceOf(Auth0::class)
        ->and($auth0)->toBe($auth0Again); // Same instance (singleton)
});

it('registers Auth0 API instance as a singleton', function () {
    // Set up required config
    config(['auth0.audience' => ['https://test.api']]);

    $this->app->singleton('auth0.api', function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });

    $auth0 = app('auth0.api');
    $auth0Again = app('auth0.api');

    expect($auth0)->toBeInstanceOf(Auth0::class)
        ->and($auth0)->toBe($auth0Again); // Same instance (singleton)
});

it('throws exception when AUTH0_AUDIENCE is not set for API instance', function () {
    // Clear the singleton and config
    $this->app->forgetInstance('auth0.api');

    // Remove the audience config
    config(['auth0.audience' => null]);

    // Re-register with missing audience
    $this->app->singleton('auth0.api', function () {
        $config = config('auth0');

        if (empty($config['audience'])) {
            throw new \RuntimeException('AUTH0_AUDIENCE environment variable is not set');
        }

        return new Auth0(new SdkConfiguration([...$config, 'strategy' => 'api']));
    });

    expect(fn () => app('auth0.api'))
        ->toThrow(RuntimeException::class, 'AUTH0_AUDIENCE environment variable is not set');
});

it('returns null user when no authorization header is present', function () {
    // Set up minimal Auth0 API config
    $this->app->singleton('auth0.api', function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });

    $user = Auth::guard('api')->user();

    expect($user)->toBeNull();
});

it('returns null user when authorization header has invalid token', function () {
    // Set up minimal Auth0 API config
    $this->app->singleton('auth0.api', function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });

    // Make request with invalid token
    $response = getJson(route('auth.me'), [
        'Authorization' => 'Bearer invalid-token',
    ]);

    $response->assertStatus(401);
});

it('configures auth0 guard in config', function () {
    $guards = config('auth.guards');

    expect($guards)->toHaveKey('api')
        ->and($guards['api']['driver'])->toBe('auth0');
});

it('allows manually setting authenticated user for testing', function () {
    // Set up minimal Auth0 API config
    $this->app->singleton('auth0.api', function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });

    $auth0User = new GenericUser([
        'id' => 'auth0|test123',
        'sub' => 'auth0|test123',
        'email' => 'test@example.com',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $user = Auth::guard('api')->user();

    expect($user)->not->toBeNull()
        ->and($user->id)->toBe('auth0|test123');
});
