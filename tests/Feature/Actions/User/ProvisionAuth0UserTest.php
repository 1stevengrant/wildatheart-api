<?php

use App\Models\User;
use Illuminate\Support\Str;
use Saloon\Http\Faking\MockClient;
use Illuminate\Support\Facades\Http;
use Saloon\Http\Faking\MockResponse;
use App\Actions\User\ProvisionAuth0User;
use App\DataTransferObjects\Auth0Profile;
use App\Exceptions\UserProvisioningException;
use App\Http\Integrations\Auth0\Requests\GetUserProfileRequest;

beforeEach(function () {
    $this->sub = 'email|' . Str::random(24);
});

function verifiedProfile(string $email = 'provision.test@example.test'): Auth0Profile
{
    return new Auth0Profile(
        email: $email,
        emailVerified: true,
        givenName: 'Provision',
        familyName: 'Test',
    );
}

it('resolves an existing user by provider_id without calling userinfo', function () {
    Http::fake();

    $existing = User::factory()->create(['provider_id' => $this->sub, 'provider' => 'auth0']);

    $user = ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    expect($user->id)->toBe($existing->id);

    Http::assertNothingSent();
});

it('resurrects a soft-deleted user matched by provider_id', function () {
    $deleted = User::factory()->create(['provider_id' => $this->sub, 'provider' => 'auth0']);
    $deleted->delete();

    $user = ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    expect($user->id)->toBe($deleted->id)
        ->and($user->trashed())->toBeFalse();
});

it('creates a new user from a verified profile', function () {
    $user = ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    expect($user->provider_id)->toBe($this->sub)
        ->and($user->provider)->toBe('auth0')
        ->and($user->email)->toBe('provision.test@example.test')
        ->and($user->given_name)->toBe('Provision')
        ->and($user->family_name)->toBe('Test');
});

it('copies the Auth0 signup phone number onto a new user', function () {
    config([
        'auth0.management.endpoint' => 'https://auth0.test/oauth/token',
        'auth0.management.audience' => 'https://auth0.test/api/v2/',
    ]);
    Http::fake(['auth0.test/oauth/token' => Http::response(['access_token' => 'management-token'])]);
    $mockClient = MockClient::global([
        GetUserProfileRequest::class => MockResponse::make([
            'user_metadata' => ['unverified_phone_number' => '+17195550123'],
        ]),
    ]);

    $user = ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    expect($user->phone)->toBe('+17195550123')
        ->and($user->fresh()->phone)->toBe('+17195550123');
    $mockClient->assertSent(GetUserProfileRequest::class);

    MockClient::destroyGlobal();
});

it('still provisions a new user when the Auth0 profile lookup fails', function () {
    config([
        'auth0.management.endpoint' => 'https://auth0.test/oauth/token',
        'auth0.management.audience' => 'https://auth0.test/api/v2/',
    ]);
    Http::fake(['auth0.test/oauth/token' => Http::response(['error' => 'access_denied'], 401)]);

    $user = ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    expect($user->exists)->toBeTrue()
        ->and($user->phone)->toBeNull();
});

it('does not look up the Auth0 profile for an existing user', function () {
    $mockClient = MockClient::global([
        GetUserProfileRequest::class => MockResponse::make(['user_metadata' => []]),
    ]);
    User::factory()->create(['provider_id' => $this->sub, 'provider' => 'auth0']);

    ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    $mockClient->assertNothingSent();

    MockClient::destroyGlobal();
});

it('links an existing active user matched by verified email', function () {
    $existing = User::factory()->create([
        'email' => 'provision.test@example.test',
        'provider_id' => null,
        'provider' => null,
    ]);

    $user = ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token');

    expect($user->id)->toBe($existing->id)
        ->and($user->provider_id)->toBe($this->sub)
        ->and($user->provider)->toBe('auth0');
});

it('refuses a soft-deleted user matched only by email', function () {
    $deleted = User::factory()->create([
        'email' => 'provision.test@example.test',
        'provider_id' => null,
        'provider' => null,
    ]);
    $deleted->delete();

    expect(fn () => ProvisionAuth0User::run($this->sub, verifiedProfile(), 'fake.jwt.token'))
        ->toThrow(fn (UserProvisioningException $exception) => expect($exception->errorCode)->toBe('account_disabled'));
});

it('refuses an unverified email', function () {
    $profile = new Auth0Profile(
        email: 'unverified@example.test',
        emailVerified: false,
        givenName: null,
        familyName: null,
    );

    expect(fn () => ProvisionAuth0User::run($this->sub, $profile, 'fake.jwt.token'))
        ->toThrow(fn (UserProvisioningException $exception) => expect($exception->errorCode)->toBe('email_not_verified'));
});

it('falls back to userinfo when the profile has no email', function () {
    Http::fake([
        config('auth0.api.userinfo_url') => Http::response([
            'sub' => $this->sub,
            'email' => 'Userinfo.Fallback@Example.test',
            'email_verified' => true,
            'given_name' => 'Userinfo',
            'family_name' => 'Fallback',
        ], 200),
    ]);

    $emptyProfile = new Auth0Profile(email: null, emailVerified: false, givenName: null, familyName: null);

    $user = ProvisionAuth0User::run($this->sub, $emptyProfile, 'fake.jwt.token');

    expect($user->email)->toBe('userinfo.fallback@example.test')
        ->and($user->given_name)->toBe('Userinfo');

    Http::assertSentCount(1);
});

it('fails when neither the profile nor userinfo yield an email', function () {
    Http::fake([
        config('auth0.api.userinfo_url') => Http::response(['sub' => $this->sub], 200),
    ]);

    $emptyProfile = new Auth0Profile(email: null, emailVerified: false, givenName: null, familyName: null);

    expect(fn () => ProvisionAuth0User::run($this->sub, $emptyProfile, 'fake.jwt.token'))
        ->toThrow(fn (UserProvisioningException $exception) => expect($exception->errorCode)->toBe('user_provisioning_failed'));
});

it('treats a failed userinfo response as no profile', function () {
    Http::fake([
        config('auth0.api.userinfo_url') => Http::response(null, 500),
    ]);

    $emptyProfile = new Auth0Profile(email: null, emailVerified: false, givenName: null, familyName: null);

    expect(fn () => ProvisionAuth0User::run($this->sub, $emptyProfile, 'fake.jwt.token'))
        ->toThrow(UserProvisioningException::class);
});

describe('Auth0Profile::fromClaims', function () {
    it('reads bare claims and lowercases the email', function () {
        $profile = Auth0Profile::fromClaims([
            'email' => 'Mixed.Case@Example.test',
            'email_verified' => true,
            'given_name' => 'Given',
            'family_name' => 'Family',
        ]);

        expect($profile->email)->toBe('mixed.case@example.test')
            ->and($profile->emailVerified)->toBeTrue()
            ->and($profile->givenName)->toBe('Given')
            ->and($profile->familyName)->toBe('Family');
    });

    it('reads namespaced claims keyed by the API audience', function () {
        $namespace = mb_rtrim((string) config('auth0.api.audience'), '/');

        $profile = Auth0Profile::fromClaims([
            "{$namespace}/email" => 'namespaced@example.test',
            "{$namespace}/email_verified" => 'true',
        ]);

        expect($profile->email)->toBe('namespaced@example.test')
            ->and($profile->emailVerified)->toBeTrue();
    });

    it('treats a missing or non-boolean email_verified claim as unverified', function () {
        expect(Auth0Profile::fromClaims(['email' => 'a@b.test'])->emailVerified)->toBeFalse()
            ->and(Auth0Profile::fromClaims(['email' => 'a@b.test', 'email_verified' => 'false'])->emailVerified)->toBeFalse()
            ->and(Auth0Profile::fromClaims(['email' => 'a@b.test', 'email_verified' => 1])->emailVerified)->toBeFalse();
    });
});
