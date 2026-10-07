<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Services\Auth0\JwtAccessTokenVerifier;
use Auth0\SDK\Exception\InvalidTokenException;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns the authenticated user data', function () {
    $user = User::factory()->create([
        'given_name' => 'Wild',
        'family_name' => 'Heart',
        'email' => 'member@wildatheart.org',
        'phone' => '+15555555555',
        'city' => 'Colorado Springs',
        'state' => 'CO',
        'postal_code' => '80903',
        'opted_into_marketing' => true,
        'profile_step' => 2,
    ]);

    mockJwtAuthentication($user);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'given_name' => 'Wild',
            'family_name' => 'Heart',
            'fullname' => 'Wild Heart',
            'email' => 'member@wildatheart.org',
            'opted_into_marketing' => true,
            'profile_step' => 2,
            'phone' => '+15555555555',
            'city' => 'Colorado Springs',
            'state' => 'CO',
            'postal_code' => '80903',
        ])
        ->assertJsonStructure(['image']);
});

it('handles users with null optional fields', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
        'phone' => null,
        'city' => null,
        'state' => null,
        'postal_code' => null,
        'profile_step' => null,
    ]);

    mockJwtAuthentication($user);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJson([
            'given_name' => null,
            'family_name' => null,
            'fullname' => 'Member',
            'phone' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'profile_step' => null,
        ]);
});

it('reports whether the user is a host', function (Closure $makeUser, bool $isHost) {
    $user = $makeUser();

    mockJwtAuthentication($user);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('is_host', $isHost);
})->with([
    'a signed-in user with no events' => [
        fn () => User::factory()->create(['agreed_to_host_agreement_date' => null]),
        false,
    ],
    'a user who agreed to the host agreement' => [
        fn () => User::factory()->create(['agreed_to_host_agreement_date' => now()]),
        true,
    ],
    'a user who owns an event' => [function () {
        $owner = User::factory()->create(['agreed_to_host_agreement_date' => null]);
        Event::withoutEvents(fn () => Event::factory()->fires()->for($owner)->create());

        return $owner;
    }, true],
    'a co-host of an event' => [function () {
        $coHost = User::factory()->create(['agreed_to_host_agreement_date' => null]);
        $event = Event::withoutEvents(fn () => Event::factory()->fires()->create());
        $event->managers()->attach($coHost->id);

        return $coHost;
    }, true],
]);

it('returns 401 without a bearer token', function () {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized();
});

it('returns 401 when JWT verification fails', function () {
    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andThrow(new InvalidTokenException('Invalid test token'));

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertUnauthorized()
        ->assertJsonPath('error', 'invalid_token');
});

it('provisions a user from verified JWT claims', function () {
    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn([
            'sub' => 'auth0|new-user',
            'email' => 'new-user@wildatheart.org',
            'email_verified' => true,
            'given_name' => 'New',
            'family_name' => 'User',
        ]);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('email', 'new-user@wildatheart.org');

    $this->assertDatabaseHas('users', [
        'provider_id' => 'auth0|new-user',
        'email' => 'new-user@wildatheart.org',
    ]);
});
