<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns 401 without a bearer token', function () {
    $owner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    $this->getJson("/api/v1/host/ownership-transfers/{$transfer->id}")
        ->assertUnauthorized();
});

it('forbids viewing a transfer addressed to a different email', function () {
    $owner = User::factory()->create();
    $newOwner = User::factory()->create();
    $someoneElse = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($newOwner->id);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($someoneElse);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/ownership-transfers/{$transfer->id}")
        ->assertForbidden();
});

it('shows a pending transfer to its intended recipient', function () {
    $owner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($newOwner->id);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($newOwner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->getJson("/api/v1/host/ownership-transfers/{$transfer->id}")
        ->assertOk()
        ->assertJsonPath('id', $transfer->id)
        ->assertJsonPath('transferred_by_name', $owner->fullname)
        ->assertJsonPath('event.id', $event->id);
});

it('forbids accepting a transfer addressed to a different email', function () {
    $owner = User::factory()->create();
    $newOwner = User::factory()->create();
    $someoneElse = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($newOwner->id);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($someoneElse);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/ownership-transfers/{$transfer->id}/accept")
        ->assertForbidden();

    expect($event->fresh()->user_id)->toBe($owner->id);
});

it('accepts a pending transfer and moves ownership to the new owner', function () {
    $owner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($newOwner->id);
    $event->load('eventType');

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($newOwner);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/ownership-transfers/{$transfer->id}/accept");

    $response->assertOk()
        ->assertJsonPath('event_id', $event->id);

    expect($event->fresh()->user_id)->toBe($newOwner->id);
    expect($transfer->fresh()->isAccepted())->toBeTrue();
});
