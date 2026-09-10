<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Middleware\Auth0ApiMiddleware;

it('caches a scalar user id for an Auth0 token', function (): void {
    configureSerializedArrayCache();

    $user = User::factory()->create(['email' => 'member@example.com']);
    Http::fake([
        '*' => Http::response(['email' => $user->email]),
    ]);

    $request = Request::create('/api/test', server: [
        'HTTP_AUTHORIZATION' => 'Bearer test-token',
    ]);

    $middleware = app(Auth0ApiMiddleware::class);

    $middleware->handle($request, fn () => response()->json(['ok' => true]));
    auth()->logout();
    $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

    expect($response->isSuccessful())->toBeTrue();

    $cacheKey = 'auth0_user_id:' . hash('sha256', 'test-token');

    expect(cache()->get($cacheKey))->toBe($user->id)
        ->and(auth()->id())->toBe($user->id);

    Http::assertSentCount(1);
});
