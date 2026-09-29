<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Mail\V1\OwnershipTransferInvitationMail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns 401 without a bearer token', function () {
    $event = Event::factory()->fires()->approved()->create();

    $this->postJson("/api/v1/host/events/{$event->id}/ownership-transfers", ['user_id' => 1])
        ->assertUnauthorized();
});

it('forbids a co-host from initiating a transfer', function () {
    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach([$coHost->id, $newOwner->id]);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/ownership-transfers", ['user_id' => $newOwner->id])
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('rejects a transfer to a user who is not a co-host', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/ownership-transfers", ['user_id' => $stranger->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');

    Mail::assertNothingSent();
});

it('rejects transferring ownership to yourself', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/ownership-transfers", ['user_id' => $owner->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');
});

it('rejects a second pending transfer for the same event', function () {
    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $anotherCoHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach([$coHost->id, $anotherCoHost->id]);

    EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $coHost->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/ownership-transfers", ['user_id' => $anotherCoHost->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');
});

it('initiates a pending ownership transfer and emails the co-host', function () {
    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($coHost->id);

    mockJwtAuthentication($owner);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/ownership-transfers", ['user_id' => $coHost->id]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('event.pending_ownership_transfer.email', $coHost->email)
        ->assertJsonPath('event.pending_ownership_transfer.transferred_by_name', $owner->fullname);

    expect(EventOwnershipTransfer::withoutGlobalScope('pending')->where('event_id', $event->id)->first())
        ->email->toBe($coHost->email);

    Mail::assertQueued(OwnershipTransferInvitationMail::class, fn ($mail) => $mail->hasTo($coHost->email));
});

it('returns 404 when cancelling with no pending transfer', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/ownership-transfers")
        ->assertNotFound();
});

it('cancels a pending ownership transfer', function () {
    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($owner)->create();
    $event->managers()->attach($coHost->id);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $coHost->email,
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($owner);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/ownership-transfers");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('event.pending_ownership_transfer', null);

    expect(EventOwnershipTransfer::withoutGlobalScope('pending')->find($transfer->id))->toBeNull();
});
