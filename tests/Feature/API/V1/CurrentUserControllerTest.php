<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use App\Services\Auth0\JwtAccessTokenVerifier;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;
use App\Jobs\WeGive\UnsubscribeFromCommunicationListJob;

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

it('opts the user into marketing and subscribes them to the WeGive list', function () {
    Bus::fake();

    authedPatch(['opted_into_marketing' => true])
        ->assertOk()
        ->assertJsonPath('opted_into_marketing', true);

    $user = User::where('provider_id', $this->sub)->first();

    expect($user->opted_into_marketing)->toBeTrue()
        ->and($user->date_opted_into_marketing)->not->toBeNull();

    Bus::assertDispatched(SubscribeToCommunicationListJob::class);
});

it('opts the user out of marketing and unsubscribes them from the WeGive list', function () {
    Bus::fake();

    authedPatch(['city' => 'Auckland'])->assertOk();

    User::where('provider_id', $this->sub)->update(['opted_into_marketing' => true]);

    authedPatch(['opted_into_marketing' => false])
        ->assertOk()
        ->assertJsonPath('opted_into_marketing', false);

    expect(User::where('provider_id', $this->sub)->first()->opted_into_marketing)->toBeFalse();

    Bus::assertDispatched(UnsubscribeFromCommunicationListJob::class);
});

it('does not dispatch WeGive jobs when the marketing preference is unchanged', function () {
    Bus::fake();

    authedPatch(['opted_into_marketing' => false])->assertOk();

    Bus::assertNotDispatched(SubscribeToCommunicationListJob::class);
    Bus::assertNotDispatched(UnsubscribeFromCommunicationListJob::class);
});

it('rejects a non boolean opted_into_marketing', function () {
    authedPatch(['opted_into_marketing' => 'maybe'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['opted_into_marketing']);
});

it('rejects blank names and phone when present', function (string $field) {
    authedPatch([$field => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors([$field]);

    authedPatch([$field => null])
        ->assertStatus(422)
        ->assertJsonValidationErrors([$field]);
})->with(['given_name', 'family_name', 'phone']);

it('rejects a phone number that is not E.164', function (string $phone) {
    authedPatch(['phone' => $phone])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
})->with(['021 555 0100', '64215550100', '+0215550100', '+1234567890123456']);
