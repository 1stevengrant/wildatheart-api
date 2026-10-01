<?php

use App\Models\User;
use App\Models\Event;
use App\Mail\HostAddedMail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    config(['app.frontend_url' => 'https://wildatheart.test']);
});

function eventWithInviteToken(User $owner, string $token = 'shareable-token'): Event
{
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->forceFill(['host_invite_token' => $token])->save();

    return $event;
}

it('returns 401 for the invite link without a bearer token', function () {
    $event = Event::factory()->fires()->approved()->create();

    $this->getJson("/api/v1/host/events/{$event->id}/invite-link")
        ->assertUnauthorized();
});

it('generates a join-as-host link for the event owner and reuses it', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    mockJwtAuthentication($owner);

    $url = $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/events/{$event->id}/invite-link")
        ->assertOk()
        ->json('url');

    $token = $event->fresh()->host_invite_token;

    expect($token)->not->toBeEmpty()
        ->and($url)->toBe("https://wildatheart.test/join-as-host/{$token}");

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/events/{$event->id}/invite-link")
        ->assertOk()
        ->assertJsonPath('url', $url);
});

it('lets a co-host copy the invite link', function () {
    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($coHost->id);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/events/{$event->id}/invite-link")
        ->assertOk()
        ->assertJsonStructure(['url']);
});

it('forbids the invite link for users who do not host the event', function () {
    $event = Event::factory()->fires()->approved()->create();

    mockJwtAuthentication(User::factory()->create());

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/events/{$event->id}/invite-link")
        ->assertForbidden();

    expect($event->fresh()->host_invite_token)->toBeNull();
});

it('does not expose the invite token when the event is serialized', function () {
    $event = eventWithInviteToken(User::factory()->create());

    expect($event->toArray())->not->toHaveKey('host_invite_token');
});

it('previews the event behind a join-as-host link', function () {
    $owner = User::factory()->create(['given_name' => 'Pat', 'family_name' => 'Owner']);
    $event = eventWithInviteToken($owner);

    mockJwtAuthentication(User::factory()->create());

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/host/join-as-host/shareable-token')
        ->assertOk()
        ->assertJsonPath('token', 'shareable-token')
        ->assertJsonPath('event.id', $event->id)
        ->assertJsonPath('invited_by_name', $owner->fullname)
        ->assertJsonPath('already_host', false);
});

it('404s for an unknown join-as-host token', function () {
    mockJwtAuthentication(User::factory()->create());

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/host/join-as-host/nope')
        ->assertNotFound();
});

it('adds the user as a co-host when they accept a join-as-host link', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $event = eventWithInviteToken($owner);

    mockJwtAuthentication($newHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/join-as-host/shareable-token/accept')
        ->assertOk()
        ->assertJsonPath('event_id', $event->id);

    expect($event->isOwnedOrManagedBy($newHost))->toBeTrue()
        ->and(EventManagerInvitation::withoutGlobalScopes()->where('event_id', $event->id)->sole()->isAccepted())->toBeTrue();

    Mail::assertQueued(HostAddedMail::class, 2);
});

it('accepts an existing pending email invitation when joining by link', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $event = eventWithInviteToken($owner);

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $newHost->email,
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($newHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/join-as-host/shareable-token/accept')
        ->assertOk();

    expect($invitation->fresh()->isAccepted())->toBeTrue()
        ->and(EventManagerInvitation::withoutGlobalScopes()->where('event_id', $event->id)->count())->toBe(1);
});

it('does nothing when an existing host accepts the link again', function () {
    $owner = User::factory()->create();
    eventWithInviteToken($owner);

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/join-as-host/shareable-token/accept')
        ->assertOk();

    expect(EventManagerInvitation::withoutGlobalScopes()->count())->toBe(0);
    Mail::assertNotQueued(HostAddedMail::class);
});
