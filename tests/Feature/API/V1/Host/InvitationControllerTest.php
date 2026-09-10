<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns 401 without a bearer token', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $newHost->email,
        'invited_by_user_id' => $owner->id,
    ]);

    $this->getJson("/api/v1/host/invitations/{$invitation->id}")
        ->assertUnauthorized();
});

it('forbids viewing an invitation addressed to a different email', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $someoneElse = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $newHost->email,
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($someoneElse);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/invitations/{$invitation->id}")
        ->assertForbidden();
});

it('shows a pending invitation to its intended recipient', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $newHost->email,
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($newHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/invitations/{$invitation->id}")
        ->assertOk()
        ->assertJsonPath('id', $invitation->id)
        ->assertJsonPath('invited_by_name', $owner->fullname)
        ->assertJsonPath('event.id', $event->id);
});

it('forbids accepting an invitation addressed to a different email', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $someoneElse = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $newHost->email,
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($someoneElse);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/invitations/{$invitation->id}/accept")
        ->assertForbidden();

    expect($event->managers()->where('user_id', $someoneElse->id)->exists())->toBeFalse();
});

it('accepts a pending invitation and adds the user as a host', function () {
    $owner = User::factory()->create();
    $newHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->load('eventType');

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $newHost->email,
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($newHost);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/invitations/{$invitation->id}/accept");

    $response->assertOk()
        ->assertJsonPath('event_id', $event->id);

    expect($event->managers()->where('user_id', $newHost->id)->exists())->toBeTrue();
    expect($invitation->fresh()->isAccepted())->toBeTrue();
});
