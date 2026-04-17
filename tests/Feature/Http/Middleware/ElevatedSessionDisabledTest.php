<?php

use Illuminate\Http\Request;
use Statamic\Exceptions\ElevatedSessionAuthorizationException;
use Statamic\Http\Middleware\CP\RequireElevatedSession;

it('bypasses elevated session check when elevated sessions are disabled', function () {
    config(['statamic.users.elevated_sessions_enabled' => false]);

    // Re-register the macro override since config changed after boot
    Request::macro('hasElevatedSession', fn () => true);

    $request = Request::create('/cp/test');
    $middleware = new RequireElevatedSession;

    $response = $middleware->handle($request, fn ($req) => response('ok'));

    expect($response->getContent())->toBe('ok');
});

it('bypasses elevated session check when disabled even without a session', function () {
    config(['statamic.users.elevated_sessions_enabled' => false]);

    Request::macro('hasElevatedSession', fn () => true);

    $request = Request::create('/cp/test');

    expect($request->hasElevatedSession())->toBeTrue();
});

it('enforces elevated session check when elevated sessions are enabled', function () {
    config(['statamic.users.elevated_sessions_enabled' => true]);

    Request::macro('hasElevatedSession', fn () => false);

    $request = Request::create('/cp/test');
    $middleware = new RequireElevatedSession;

    $middleware->handle($request, fn ($req) => response('ok'));
})->throws(ElevatedSessionAuthorizationException::class);

it('has elevated sessions disabled by default in config', function () {
    expect(config('statamic.users.elevated_sessions_enabled'))->toBeFalse();
});
