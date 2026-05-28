<?php

use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    config(['auth0.base_url' => 'https://example.auth0.com']);
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

    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([
            'email' => $user->email,
            'sub' => 'auth0|abc123',
        ]),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer fake-token')
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

    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([
            'email' => $user->email,
            'sub' => 'auth0|abc123',
        ]),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer fake-token')
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

it('returns 401 without a bearer token', function () {
    $this->getJson('/api/v1/me')
        ->assertUnauthorized();
});

it('returns 401 when Auth0 rejects the token', function () {
    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([], 401),
    ]);

    $this->withHeader('Authorization', 'Bearer bad-token')
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

it('returns 401 when the Auth0 email has no matching local user', function () {
    Http::fake([
        'https://example.auth0.com/userinfo' => Http::response([
            'email' => 'unknown@wildatheart.org',
            'sub' => 'auth0|abc123',
        ]),
    ]);

    $this->withHeader('Authorization', 'Bearer fake-token')
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});
