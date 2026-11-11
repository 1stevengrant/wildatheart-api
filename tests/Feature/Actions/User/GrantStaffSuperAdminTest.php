<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use App\Actions\User\ProvisionAuth0User;
use App\DataTransferObjects\Auth0Profile;
use App\Exceptions\UserProvisioningException;
use App\Http\Controllers\Auth\CallbackController;

beforeEach(function () {
    Bus::fake();
    app()->detectEnvironment(fn () => 'staging');
    $this->sub = 'auth0|' . Str::random(24);
});

function staffProfile(string $email, bool $verified = true): Auth0Profile
{
    return new Auth0Profile(email: $email, emailVerified: $verified, givenName: 'Staff', familyName: 'Member');
}

function signInThroughWebCallback(string $email, bool $verified, string $sub = 'auth0|web'): User
{
    $controller = (new ReflectionClass(CallbackController::class))->newInstanceWithoutConstructor();

    return (new ReflectionMethod($controller, 'findOrCreateUser'))->invoke($controller, [
        'email' => $email,
        'email_verified' => $verified,
        'given_name' => 'Staff',
        'family_name' => 'Member',
        'sub' => $sub,
    ]);
}

it('makes a new staff user a super admin on staging', function (string $email) {
    $user = ProvisionAuth0User::run($this->sub, staffProfile($email), 'fake.jwt.token');

    expect($user->fresh()->super)->toBeTrue();
})->with([
    'wildatheart.org' => 'new.staff@wildatheart.org',
    'ghijk.co.uk' => 'new.staff@ghijk.co.uk',
    'upper case domain' => 'New.Staff@WildAtHeart.ORG',
]);

it('also does it locally', function () {
    app()->detectEnvironment(fn () => 'local');

    $user = ProvisionAuth0User::run($this->sub, staffProfile('local.staff@wildatheart.org'), 'fake.jwt.token');

    expect($user->fresh()->super)->toBeTrue();
});

it('ignores lookalike domains', function (string $email) {
    $user = ProvisionAuth0User::run($this->sub, staffProfile($email), 'fake.jwt.token');

    expect($user->fresh()->super)->toBeFalse();
})->with([
    'prefixed domain' => 'x@evilwildatheart.org',
    'domain as a subdomain' => 'x@wildatheart.org.evil.com',
    'subdomain of ours' => 'x@mail.wildatheart.org',
    'domain in the local part' => 'wildatheart.org@evil.com',
    'other domain' => 'x@example.com',
]);

it('never does it in production', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['auth.staff_super_admins.environments' => ['production', 'staging', 'local']]);

    $user = ProvisionAuth0User::run($this->sub, staffProfile('prod.staff@wildatheart.org'), 'fake.jwt.token');
    $webUser = signInThroughWebCallback('prod.web@wildatheart.org', verified: true);

    expect($user->fresh()->super)->toBeFalse()
        ->and($webUser->fresh()->super)->toBeFalse();
});

it('does nothing in environments outside the configured list', function () {
    app()->detectEnvironment(fn () => 'testing');

    $user = ProvisionAuth0User::run($this->sub, staffProfile('tester@wildatheart.org'), 'fake.jwt.token');

    expect($user->fresh()->super)->toBeFalse();
});

it('reads the domains from config', function () {
    config(['auth.staff_super_admins.domains' => ['example.org']]);

    $staff = ProvisionAuth0User::run($this->sub, staffProfile('someone@example.org'), 'fake.jwt.token');
    $wildAtHeart = ProvisionAuth0User::run('auth0|other', staffProfile('someone@wildatheart.org'), 'fake.jwt.token');

    expect($staff->fresh()->super)->toBeTrue()
        ->and($wildAtHeart->fresh()->super)->toBeFalse();
});

it('refuses an unverified API signup with a staff address before a user exists', function () {
    expect(fn () => ProvisionAuth0User::run($this->sub, staffProfile('unverified@wildatheart.org', verified: false), 'fake.jwt.token'))
        ->toThrow(UserProvisioningException::class);

    expect(User::where('email', 'unverified@wildatheart.org')->exists())->toBeFalse();
});

it('keeps an unverified web signup with a staff address an ordinary user', function () {
    $user = signInThroughWebCallback('unverified.web@wildatheart.org', verified: false);

    expect($user->fresh()->super)->toBeFalse();
});

it('promotes a staff user at their first sign in after verifying', function () {
    signInThroughWebCallback('later@wildatheart.org', verified: false);

    $user = signInThroughWebCallback('later@wildatheart.org', verified: true);

    expect($user->fresh()->super)->toBeTrue();
});

it('promotes an existing staff user when they first sign in to the API', function () {
    $existing = User::factory()->create(['email' => 'existing@wildatheart.org', 'super' => false]);

    ProvisionAuth0User::run($this->sub, staffProfile('existing@wildatheart.org'), 'fake.jwt.token');

    expect($existing->fresh()->super)->toBeTrue();
});

it('promotes an already linked staff user on their next API request with a verified address', function () {
    $linked = User::factory()->create(['email' => 'linked@wildatheart.org', 'provider_id' => $this->sub, 'super' => false]);

    ProvisionAuth0User::run($this->sub, staffProfile('linked@wildatheart.org', verified: false), 'fake.jwt.token');
    expect($linked->fresh()->super)->toBeFalse();

    ProvisionAuth0User::run($this->sub, staffProfile('linked@wildatheart.org'), 'fake.jwt.token');
    expect($linked->fresh()->super)->toBeTrue();
});
