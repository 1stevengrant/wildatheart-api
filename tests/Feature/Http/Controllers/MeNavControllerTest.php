<?php

use App\Models\User;
use Illuminate\Auth\GenericUser;

use function Pest\Laravel\getJson;

use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('returns guest menu when no user is found for provider_id', function () {
    $auth0User = new GenericUser([
        'id' => 'auth0|nonexistent',
        'sub' => 'auth0|nonexistent',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me.nav'));

    $response->assertOk()
        ->assertJsonStructure([
            'urlToProfile',
            'primaryItems',
            'secondaryItems',
        ])
        ->assertJson([
            'primaryItems' => [],
            'secondaryItems' => [],
        ]);

    expect($response->json('urlToProfile'))->toContain('auth.wildatheart.org/authorize');
});

it('returns attendee menu for user with attendee role', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|attendee123',
        'provider' => 'auth0',
    ]);
    $user->assignRole(User::ROLE_ATTENDEE);

    $auth0User = new GenericUser([
        'id' => 'auth0|attendee123',
        'sub' => 'auth0|attendee123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me.nav'));

    $response->assertOk()
        ->assertJsonStructure([
            'urlToProfile',
            'primaryItems' => [
                '*' => ['label', 'href', 'iconName'],
            ],
            'secondaryItems' => [
                '*' => ['label', 'href'],
            ],
        ]);

    $data = $response->json();

    expect($data['primaryItems'])->toHaveCount(1)
        ->and($data['primaryItems'][0]['label'])->toBe('My Giving')
        ->and($data['primaryItems'][0]['iconName'])->toBe('heart')
        ->and($data['secondaryItems'])->toHaveCount(1)
        ->and($data['secondaryItems'][0]['label'])->toBe('Host an Event');
});

it('returns host menu for user with host role', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|host123',
        'provider' => 'auth0',
    ]);
    $user->assignRole(User::ROLE_HOST);

    $auth0User = new GenericUser([
        'id' => 'auth0|host123',
        'sub' => 'auth0|host123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me.nav'));

    $response->assertOk();

    $data = $response->json();

    expect($data['primaryItems'])->toHaveCount(2)
        ->and($data['primaryItems'][0]['label'])->toBe('My Giving')
        ->and($data['primaryItems'][0]['iconName'])->toBe('heart')
        ->and($data['primaryItems'][1]['label'])->toBe('My Events')
        ->and($data['primaryItems'][1]['iconName'])->toBe('bookSquare')
        ->and($data['secondaryItems'])->toHaveCount(1)
        ->and($data['secondaryItems'][0]['label'])->toBe('Host an Event');
});

it('returns host menu when user has both host and attendee roles', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|both123',
        'provider' => 'auth0',
    ]);
    $user->assignRole(User::ROLE_HOST);
    $user->assignRole(User::ROLE_ATTENDEE);

    $auth0User = new GenericUser([
        'id' => 'auth0|both123',
        'sub' => 'auth0|both123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me.nav'));

    $response->assertOk();

    $data = $response->json();

    // Host role takes precedence
    expect($data['primaryItems'])->toHaveCount(2)
        ->and($data['primaryItems'][1]['label'])->toBe('My Events');
});

it('includes correct urls in menu items', function () {
    $user = User::factory()->create([
        'provider_id' => 'auth0|urltest123',
        'provider' => 'auth0',
    ]);
    $user->assignRole(User::ROLE_HOST);

    $auth0User = new GenericUser([
        'id' => 'auth0|urltest123',
        'sub' => 'auth0|urltest123',
    ]);

    Auth::guard('api')->setUser($auth0User);

    $response = getJson(route('auth.me.nav'));

    $data = $response->json();

    expect($data['urlToProfile'])->toContain('my.wildatheart.org/wah/settings?tab=2')
        ->and($data['primaryItems'][0]['href'])->toContain('my.wildatheart.org/wah/settings?tab=1')
        ->and($data['primaryItems'][1]['href'])->toBe('https://local.wildatheart.org/events/active')
        ->and($data['secondaryItems'][0]['href'])->toBe('https://local.wildatheart.org/host/select');
});
