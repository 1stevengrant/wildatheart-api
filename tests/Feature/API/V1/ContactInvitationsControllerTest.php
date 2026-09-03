<?php

use App\Models\User;
use App\Models\Event;
use App\Models\ContactInvitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Mail\ContactInvitationMail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\Auth0\JwtAccessTokenVerifier;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    Storage::fake('s3');
    $this->host = User::factory()->create(['email' => 'host@example.test']);
    $this->event = Event::factory()->fires()->approved()->for($this->host)->create(['name' => 'Alpine Fires']);
    mockJwtAuthentication($this->host);
});

function invitationPayload(array $overrides = []): array
{
    return array_merge([
        'given_name' => 'River', 'family_name' => 'Stone', 'email' => 'river.stone@example.test',
        'phone' => '+64 21 555 0101', 'event_ids' => [test()->event->id],
    ], $overrides);
}

function pendingContactInvitation(User $host, Event $event, string $email, string $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789'): ContactInvitation
{
    $normalizedEmail = mb_strtolower(str_replace(' ', '', $email));
    $invitation = ContactInvitation::query()->create([
        'invited_by_user_id' => $host->id,
        'existing_user_id' => User::query()->where('email', $normalizedEmail)->value('id'),
        'email' => $normalizedEmail, 'given_name' => 'Submitted', 'family_name' => 'Person',
        'phone' => '+64 21 555 9999', 'token_hash' => ContactInvitation::tokenHash($token),
        'active_key' => hash('sha256', "{$host->id}|{$normalizedEmail}"), 'expires_at' => now()->addDays(7),
    ]);
    $invitation->events()->attach($event);

    return $invitation;
}

function mockInvitationIdentity(string $email, string $subject = 'auth0|contact'): void
{
    test()->mock(JwtAccessTokenVerifier::class)->shouldReceive('verify')->andReturn([
        'sub' => $subject, 'email' => $email, 'email_verified' => true,
        'given_name' => 'Auth0', 'family_name' => 'Person',
    ]);
}

it('queues a generic seven day invitation without creating user or interest data', function () {
    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->post('/api/v1/host/contact-invitations', invitationPayload(['image' => UploadedFile::fake()->image('profile.jpg')]))
        ->assertAccepted()->assertJsonPath('status', 'invitation_pending')->assertJsonMissingPath('existing_account');
    $invitation = ContactInvitation::query()->findOrFail($response->json('invitation_id'));

    expect($invitation->expires_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()))->toBeTrue()
        ->and($invitation->token_hash)->toHaveLength(64)->and($invitation->events)->toHaveCount(1)
        ->and(User::query()->where('email', 'river.stone@example.test')->exists())->toBeFalse()
        ->and(DB::table('event_user_interests')->where('event_id', $this->event->id)->exists())->toBeFalse();
    Storage::disk('s3')->assertExists($invitation->staged_image_path);
    expect(Storage::disk('s3')->getVisibility($invitation->staged_image_path))->toBe('private');
    Mail::assertQueued(ContactInvitationMail::class, fn (ContactInvitationMail $mail): bool => mb_strlen($mail->token) === 43 && ContactInvitation::tokenHash($mail->token) === $invitation->token_hash);
});

it('uses the same generic response for an existing account without associating it', function () {
    $existing = User::factory()->create(['email' => 'river.stone@example.test']);
    $this->withHeaders(jwtAuthenticationHeaders())->postJson('/api/v1/host/contact-invitations', invitationPayload())
        ->assertAccepted()->assertJsonPath('status', 'invitation_pending')->assertJsonMissingPath('existing_account');
    expect($existing->interestedEvents)->toBeEmpty();
});

it('replaces and rotates an active duplicate invitation', function () {
    $firstResponse = $this->withHeaders(jwtAuthenticationHeaders())->postJson('/api/v1/host/contact-invitations', invitationPayload());
    $first = ContactInvitation::query()->findOrFail($firstResponse->json('invitation_id'));
    $secondResponse = $this->withHeaders(jwtAuthenticationHeaders())->postJson('/api/v1/host/contact-invitations', invitationPayload());
    $second = ContactInvitation::query()->findOrFail($secondResponse->json('invitation_id'));
    expect($first->fresh()->cancelled_at)->not->toBeNull()->and($first->fresh()->active_key)->toBeNull()
        ->and($second->id)->not->toBe($first->id)->and($second->token_hash)->not->toBe($first->token_hash);
});

it('previews consent details without account enumeration', function () {
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    pendingContactInvitation($this->host, $this->event, 'river.stone@example.test', $token);
    $this->postJson('/api/v1/contact-invitations/preview', ['token' => $token])->assertOk()
        ->assertJsonPath('host_name', $this->host->fullname)->assertJsonPath('events.0.name', 'Alpine Fires')
        ->assertJsonMissingPath('existing_account')->assertJsonMissingPath('email');
});

it('accepts an existing account with normalized email and preserves its profile', function () {
    $existing = User::factory()->create([
        'email' => 'river.stone@example.test', 'provider_id' => 'auth0|contact', 'given_name' => 'Original',
        'family_name' => 'Name', 'phone' => '+64 21 111 1111', 'image' => 'users/original.jpg',
    ]);
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    $invitation = pendingContactInvitation($this->host, $this->event, $existing->email, $token);
    $invitation->update(['staged_image_path' => 'contact-invitations/staged.jpg']);
    Storage::disk('s3')->put('contact-invitations/staged.jpg', UploadedFile::fake()->image('staged.jpg')->get());
    mockInvitationIdentity(' River.Stone@Example.Test ');

    $response = $this->withHeader('Authorization', 'Bearer verified-token')
        ->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertOk()->assertJsonPath('status', 'accepted');
    $existing->refresh();
    expect($existing->given_name)->toBe('Original')->and($existing->family_name)->toBe('Name')
        ->and($existing->phone)->toBe('+64 21 111 1111')->and($existing->image)->toBe('users/original.jpg')
        ->and($response->json('data.0.user_id'))->toBe($existing->id)->and($invitation->fresh()->accepted_at)->not->toBeNull();
    Storage::disk('s3')->assertMissing('contact-invitations/staged.jpg');
});

it('creates a user only on acceptance and sanitizes the staged image', function () {
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    $invitation = pendingContactInvitation($this->host, $this->event, 'new.person@example.test', $token);
    $invitation->update(['staged_image_path' => 'contact-invitations/staged.png']);
    Storage::disk('s3')->put('contact-invitations/staged.png', UploadedFile::fake()->image('staged.png')->get());
    mockInvitationIdentity('NEW.PERSON@EXAMPLE.TEST');

    $this->withHeader('Authorization', 'Bearer verified-token')
        ->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertOk()->assertJsonPath('status', 'accepted');
    $user = User::query()->where('email', 'new.person@example.test')->firstOrFail();
    expect($user->image)->toStartWith('users/')
        ->and(DB::table('event_user_interests')->where('user_id', $user->id)->where('event_id', $this->event->id)->count())->toBe(1);
    Storage::disk('s3')->assertExists($user->image);
    Storage::disk('s3')->assertMissing('contact-invitations/staged.png');
    expect(Storage::disk('s3')->getVisibility($user->image))->toBe('public');
});

it('is replay safe after acceptance', function () {
    $user = User::factory()->create(['email' => 'river.stone@example.test', 'provider_id' => 'auth0|contact']);
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    pendingContactInvitation($this->host, $this->event, $user->email, $token);
    mockInvitationIdentity($user->email);
    $this->withHeader('Authorization', 'Bearer verified-token')->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertJsonPath('status', 'accepted');
    $this->withHeader('Authorization', 'Bearer verified-token')->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertJsonPath('status', 'already_accepted');
    expect(DB::table('event_user_interests')->where('user_id', $user->id)->where('event_id', $this->event->id)->count())->toBe(1);
});

it('declines and cleans up without creating contact data', function () {
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    $invitation = pendingContactInvitation($this->host, $this->event, 'river.stone@example.test', $token);
    $invitation->update(['staged_image_path' => 'contact-invitations/staged.jpg']);
    Storage::disk('s3')->put('contact-invitations/staged.jpg', 'image');
    $this->postJson('/api/v1/contact-invitations/decline', ['token' => $token])->assertOk()->assertJsonPath('status', 'declined');
    expect($invitation->fresh()->declined_at)->not->toBeNull()->and(User::query()->where('email', $invitation->email)->exists())->toBeFalse();
    Storage::disk('s3')->assertMissing('contact-invitations/staged.jpg');
});

it('does not decline or clean up an expired invitation', function () {
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    $invitation = pendingContactInvitation($this->host, $this->event, 'river.stone@example.test', $token);
    $invitation->update([
        'expires_at' => now()->subSecond(),
        'staged_image_path' => 'contact-invitations/expired.jpg',
    ]);
    Storage::disk('s3')->put('contact-invitations/expired.jpg', 'image');

    $this->postJson('/api/v1/contact-invitations/decline', ['token' => $token])->assertGone();

    expect($invitation->fresh()->declined_at)->toBeNull();
    Storage::disk('s3')->assertExists('contact-invitations/expired.jpg');
});

it('allows the inviting host to cancel', function () {
    $invitation = pendingContactInvitation($this->host, $this->event, 'river.stone@example.test');
    $this->withHeaders(jwtAuthenticationHeaders())->deleteJson("/api/v1/host/contact-invitations/{$invitation->id}")
        ->assertOk()->assertJsonPath('status', 'cancelled');
    expect($invitation->fresh()->cancelled_at)->not->toBeNull();
});

it('rejects expired invitations and mismatched verified identities', function () {
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    $invitation = pendingContactInvitation($this->host, $this->event, 'river.stone@example.test', $token);
    $invitation->update(['expires_at' => now()->subSecond()]);
    $this->postJson('/api/v1/contact-invitations/preview', ['token' => $token])->assertGone();
    $invitation->update(['expires_at' => now()->addDay()]);
    mockInvitationIdentity('someone.else@example.test');
    $this->withHeader('Authorization', 'Bearer verified-token')->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertForbidden();
    expect($invitation->fresh()->accepted_at)->toBeNull();
});

it('requires host authentication and validates invitation uploads', function () {
    $this->postJson('/api/v1/host/contact-invitations', invitationPayload())->assertUnauthorized();

    $this->withHeaders(jwtAuthenticationHeaders())->post('/api/v1/host/contact-invitations', invitationPayload([
        'image' => UploadedFile::fake()->create('profile.pdf', 100, 'application/pdf'),
    ]))->assertUnprocessable()->assertJsonValidationErrors('image');

    $this->withHeaders(jwtAuthenticationHeaders())->post('/api/v1/host/contact-invitations', invitationPayload([
        'image' => UploadedFile::fake()->image('profile.jpg')->size(5121),
    ]))->assertUnprocessable()->assertJsonValidationErrors('image');
});

it('rejects an unverified Auth0 email without provisioning a user', function () {
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    pendingContactInvitation($this->host, $this->event, 'new.person@example.test', $token);
    $this->mock(JwtAccessTokenVerifier::class)->shouldReceive('verify')->andReturn([
        'sub' => 'auth0|contact', 'email' => 'new.person@example.test', 'email_verified' => false,
    ]);

    $this->withHeader('Authorization', 'Bearer verified-token')
        ->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertForbidden();

    expect(User::query()->where('email', 'new.person@example.test')->exists())->toBeFalse();
});

it('revalidates event control at acceptance time', function () {
    $user = User::factory()->create(['email' => 'river.stone@example.test', 'provider_id' => 'auth0|contact']);
    $token = 'abcdefghijklmnopqrstuvwxyzABCDEFGH123456789';
    pendingContactInvitation($this->host, $this->event, $user->email, $token);
    $newOwner = User::factory()->create();
    $this->event->update(['user_id' => $newOwner->id]);
    mockInvitationIdentity($user->email);

    $this->withHeader('Authorization', 'Bearer verified-token')
        ->postJson('/api/v1/contact-invitations/accept', ['token' => $token])->assertGone();

    expect(DB::table('event_user_interests')->where('user_id', $user->id)->exists())->toBeFalse();
});

it('cleans staged images from expired invitations using the scheduled command', function () {
    $invitation = pendingContactInvitation($this->host, $this->event, 'river.stone@example.test');
    $invitation->update(['expires_at' => now()->subMinute(), 'staged_image_path' => 'contact-invitations/expired.jpg']);
    Storage::disk('s3')->put('contact-invitations/expired.jpg', 'image');

    $this->artisan('contact-invitations:cleanup')->assertSuccessful();

    Storage::disk('s3')->assertMissing('contact-invitations/expired.jpg');
    expect($invitation->fresh()->active_key)->toBeNull()->and($invitation->fresh()->staged_image_path)->toBeNull();
});
