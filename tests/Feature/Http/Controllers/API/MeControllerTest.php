<?php

use App\Models\User;
use Auth0\SDK\Auth0;
use Illuminate\Auth\GenericUser;

use function Pest\Laravel\getJson;

use Illuminate\Support\Facades\Auth;
use Auth0\SDK\Configuration\SdkConfiguration;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Create a minimal Auth0 instance for testing (won't actually validate tokens)
    $this->app->singleton(Auth0::class, function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });
});

it('returns 401 when not authenticated', function () {
    $response = getJson(route('auth.me'));

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

it('returns auth0 sub and null user when user not found by provider_id', function () {
    $auth0User = new GenericUser([
        'id' => 'auth0|nonexistent',
        'sub' => 'auth0|nonexistent',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me'));

    $response->assertOk()
        ->assertJson([
            'auth0' => [
                'sub' => 'auth0|nonexistent',
            ],
            'user' => null,
        ]);
});

it('returns user when found by provider_id', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|existing123',
        'provider' => 'auth0',
        'email' => 'existing@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|existing123',
        'sub' => 'auth0|existing123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me'));

    $response->assertOk()
        ->assertJsonPath('auth0.sub', 'auth0|existing123')
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', 'existing@example.com');
});

it('links user by email when provider_id not found but email matches', function () {
    $user = User::factory()->create([
        'provider_id' => null,
        'provider' => null,
        'email' => 'linkme@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|newlink123',
        'sub' => 'auth0|newlink123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me', ['email' => 'linkme@example.com']));

    $response->assertOk()
        ->assertJsonPath('auth0.sub', 'auth0|newlink123')
        ->assertJsonPath('user.id', $user->id);

    // Verify the user was linked
    $user->refresh();
    expect($user->provider_id)->toBe('auth0|newlink123')
        ->and($user->provider)->toBe('auth0');
});

it('does not link user when email provided but no matching user exists', function () {
    $auth0User = new GenericUser([
        'id' => 'auth0|orphan123',
        'sub' => 'auth0|orphan123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me', ['email' => 'nonexistent@example.com']));

    $response->assertOk()
        ->assertJson([
            'auth0' => [
                'sub' => 'auth0|orphan123',
            ],
            'user' => null,
        ]);
});

it('returns user data with expected structure', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|structure123',
        'provider' => 'auth0',
        'name' => 'Test User',
        'email' => 'structure@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|structure123',
        'sub' => 'auth0|structure123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me'));

    $response->assertOk()
        ->assertJsonStructure([
            'auth0' => ['sub'],
            'user' => [
                'id',
                'name',
                'email',
            ],
        ]);
});
