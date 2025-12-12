<?php

use App\Models\User;
use Auth0\SDK\Auth0;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;
use Auth0\SDK\Configuration\SdkConfiguration;
use App\Http\Controllers\Concerns\ResolvesAuthenticatedUser;

// Create a test class that uses the trait
class TestControllerWithResolvesAuthenticatedUser
{
    use ResolvesAuthenticatedUser;

    public function testGetAuthenticatedUser(?string $email = null): ?User
    {
        return $this->getAuthenticatedUser($email);
    }

    public function testGetAuth0Sub(): ?string
    {
        return $this->getAuth0Sub();
    }
}

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Create a minimal Auth0 instance for testing
    $this->app->singleton(Auth0::class, function () {
        return new Auth0(new SdkConfiguration([
            'domain' => 'test.auth0.com',
            'clientId' => 'test-client-id',
            'audience' => ['https://test.api'],
            'strategy' => 'api',
        ]));
    });

    $this->controller = new TestControllerWithResolvesAuthenticatedUser;
});

it('returns null when no auth0 user is authenticated', function () {
    $result = $this->controller->testGetAuthenticatedUser();

    expect($result)->toBeNull();
});

it('returns null sub when no auth0 user is authenticated', function () {
    $result = $this->controller->testGetAuth0Sub();

    expect($result)->toBeNull();
});

it('returns user when found by provider_id', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|test123',
        'provider' => 'auth0',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|test123',
        'sub' => 'auth0|test123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $result = $this->controller->testGetAuthenticatedUser();

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($user->id);
});

it('returns auth0 sub from authenticated user', function () {
    $auth0User = new GenericUser([
        'id' => 'auth0|subtest123',
        'sub' => 'auth0|subtest123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $result = $this->controller->testGetAuth0Sub();

    expect($result)->toBe('auth0|subtest123');
});

it('returns null when provider_id does not match any user', function () {
    User::factory()->create([
        'provider_id' => 'auth0|different',
        'provider' => 'auth0',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|nomatch',
        'sub' => 'auth0|nomatch',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $result = $this->controller->testGetAuthenticatedUser();

    expect($result)->toBeNull();
});

it('finds user by email when provider_id not found', function () {
    $user = User::factory()->create([
        'provider_id' => null,
        'email' => 'fallback@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|newfallback',
        'sub' => 'auth0|newfallback',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $result = $this->controller->testGetAuthenticatedUser('fallback@example.com');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($user->id);
});

it('links provider_id when user found by email', function () {
    $user = User::factory()->create([
        'provider_id' => null,
        'provider' => null,
        'email' => 'link@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|linktest',
        'sub' => 'auth0|linktest',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $this->controller->testGetAuthenticatedUser('link@example.com');

    $user->refresh();

    expect($user->provider_id)->toBe('auth0|linktest')
        ->and($user->provider)->toBe('auth0');
});

it('does not create activity log when linking accounts', function () {
    $user = User::factory()->create([
        'provider_id' => null,
        'provider' => null,
        'email' => 'nolog@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|nologtest',
        'sub' => 'auth0|nologtest',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $initialActivityCount = \Spatie\Activitylog\Models\Activity::count();

    $this->controller->testGetAuthenticatedUser('nolog@example.com');

    $finalActivityCount = \Spatie\Activitylog\Models\Activity::count();

    expect($finalActivityCount)->toBe($initialActivityCount);
});

it('prioritizes provider_id over email lookup', function () {
    $userByProviderId = User::factory()->create([
        'provider_id' => 'auth0|priority',
        'provider' => 'auth0',
        'email' => 'different@example.com',
    ]);

    $userByEmail = User::factory()->create([
        'provider_id' => null,
        'email' => 'priority@example.com',
    ]);

    $auth0User = new GenericUser([
        'id' => 'auth0|priority',
        'sub' => 'auth0|priority',
    ]);

    Auth::guard('api')->setUser($auth0User);

    // Even though we pass the email of another user, provider_id match takes precedence
    $result = $this->controller->testGetAuthenticatedUser('priority@example.com');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($userByProviderId->id);
});
