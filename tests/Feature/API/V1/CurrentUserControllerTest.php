<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use App\Services\Auth0\JwtAccessTokenVerifier;

beforeEach(function () {
    $this->sub = 'email|' . Str::random(24);

    $this->mock(JwtAccessTokenVerifier::class)
        ->shouldReceive('verify')
        ->andReturn([
            'sub' => $this->sub,
            'email' => 'profile.user@example.test',
            'email_verified' => true,
            'given_name' => 'Profile',
            'family_name' => 'User',
        ]);
});

function authedPatch(array $payload): TestResponse
{
    return test()->withHeaders(['Authorization' => 'Bearer fake.jwt.token'])
        ->patchJson('/api/v1/users/me', $payload);
}

it('updates all profile fields and returns the V1 user payload', function () {
    authedPatch([
        'given_name' => 'Steven',
        'family_name' => 'Grant',
        'phone' => '+64215550100',
        'city' => 'Auckland',
        'state' => 'Auckland',
        'country_code' => 'NZ',
        'postal_code' => '1010',
    ])
        ->assertOk()
        ->assertJson([
            'email' => 'profile.user@example.test',
            'given_name' => 'Steven',
            'family_name' => 'Grant',
            'phone' => '+64215550100',
            'city' => 'Auckland',
            'state' => 'Auckland',
            'country' => 'New Zealand',
            'country_code' => 'NZ',
            'postal_code' => '1010',
        ]);

    $user = User::where('provider_id', $this->sub)->first();

    expect($user->phone)->toBe('+64215550100')
        ->and($user->city)->toBe('Auckland')
        ->and($user->country_code)->toBe('NZ')
        ->and($user->postal_code)->toBe('1010');
});

it('accepts a partial update without touching unspecified columns', function () {
    authedPatch(['city' => 'Auckland'])->assertOk();
    $user = User::where('provider_id', $this->sub)->first();
    $originalGivenName = $user->given_name;
    $originalFamilyName = $user->family_name;

    authedPatch(['city' => 'Wellington'])->assertOk();

    $user->refresh();

    expect($user->city)->toBe('Wellington')
        ->and($user->given_name)->toBe($originalGivenName)
        ->and($user->family_name)->toBe($originalFamilyName);
});

it('preserves existing phone when phone key is omitted', function () {
    authedPatch(['phone' => '+64215550100'])->assertOk();

    authedPatch(['city' => 'Auckland'])->assertOk();

    $user = User::where('provider_id', $this->sub)->first();

    expect($user->phone)->toBe('+64215550100')
        ->and($user->city)->toBe('Auckland');
});

it('rejects an invalid country_code', function () {
    authedPatch(['country_code' => 'nz'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['country_code']);

    authedPatch(['country_code' => 'NZL'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['country_code']);

    authedPatch(['country_code' => '12'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['country_code']);
});

it('rejects an empty payload', function () {
    authedPatch([])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['payload']);
});

it('rejects an unauthenticated request', function () {
    $this->patchJson('/api/v1/users/me', ['city' => 'Auckland'])
        ->assertUnauthorized()
        ->assertJsonPath('error', 'missing_bearer');
});

it('leaves country null when country_code is unresolvable', function () {
    authedPatch(['country_code' => 'XK'])
        ->assertOk()
        ->assertJsonPath('country_code', 'XK')
        ->assertJsonPath('country', null);
});
