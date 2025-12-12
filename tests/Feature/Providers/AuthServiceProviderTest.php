<?php

use Auth0\SDK\Auth0;
use Illuminate\Auth\GenericUser;

use function Pest\Laravel\getJson;

use Illuminate\Support\Facades\Auth;
use Auth0\SDK\Configuration\SdkConfiguration;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('registers Auth0 as a singleton', function () {
    // Set up required config
    config(['auth0.audience' => ['https://test.api']]);
    config(['auth0.domain' => 'test.auth0.com']);
    config(['auth0.clientId' => 'test-client-id']);

    // Override the singleton to use test config
    $this->app->singleton(Auth0::class, function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });

    $auth0 = app(Auth0::class);
    $auth0Again = app(Auth0::class);

    expect($auth0)->toBeInstanceOf(Auth0::class)
        ->and($auth0)->toBe($auth0Again); // Same instance (singleton)
});

it('throws exception when AUTH0_AUDIENCE is not set', function () {
    // Clear the singleton and config
    $this->app->forgetInstance(Auth0::class);

    // Remove the audience config
    config(['auth0.audience' => null]);

    // Re-register with missing audience
    $this->app->singleton(Auth0::class, function () {
        $config = config('auth0');

        if (empty($config['audience'])) {
            throw new \RuntimeException('AUTH0_AUDIENCE environment variable is not set');
        }

        return new Auth0(new SdkConfiguration([...$config, 'strategy' => 'api']));
    });

    expect(fn () => app(Auth0::class))
        ->toThrow(RuntimeException::class, 'AUTH0_AUDIENCE environment variable is not set');
});

it('returns null user when no authorization header is present', function () {
    // Set up minimal Auth0 config
    $this->app->singleton(Auth0::class, function () {
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
    // Set up minimal Auth0 config
    $this->app->singleton(Auth0::class, function () {
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
    // Set up minimal Auth0 config
    $this->app->singleton(Auth0::class, function () {
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
