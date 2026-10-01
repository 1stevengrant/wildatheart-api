<?php

use App\Models\User;
use App\Models\Event;
use App\Mail\HostRemovedMail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Models\EventOwnershipTransfer;
use App\Actions\Event\TransferEventOwnership;

it('returns 401 without a bearer token', function () {
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->create());
    $coHost = User::factory()->create();
    $event->managers()->attach($coHost->id);

    $this->deleteJson("/api/v1/host/events/{$event->id}/managers/{$coHost->id}")
        ->assertUnauthorized();
});

it('forbids a co-host from removing another host', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $other = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($owner)->create());
    $event->managers()->attach([$coHost->id, $other->id]);

    mockJwtAuthentication($coHost);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$other->id}")
        ->assertForbidden();
});

it('allows the owner to remove an accepted co-host', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($owner)->create());
    $event->managers()->attach($coHost->id);

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$coHost->id}")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($event->fresh()->managers()->pluck('users.id')->all())->toEqual([]);
    Mail::assertQueued(HostRemovedMail::class);
});

it('allows the owner to cancel a pending invitation', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($owner)->create());
    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => 'pending@wildatheart.org',
        'invited_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$invitation->id}")
        ->assertOk();

    expect(EventManagerInvitation::where('id', $invitation->id)->exists())->toBeFalse();
});

it('keeps a pending ownership transfer in the response after removing a host', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $coHost = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($owner)->create());
    $event->managers()->attach($coHost->id);
    EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => 'next-owner@wildatheart.org',
        'transferred_by_user_id' => $owner->id,
    ]);

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$coHost->id}")
        ->assertOk()
        ->assertJsonPath('event.pending_ownership_transfer.email', 'next-owner@wildatheart.org');
});

it('does not allow removing the owner', function () {
    $owner = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($owner)->create());

    mockJwtAuthentication($owner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$owner->id}")
        ->assertUnprocessable();
});

it('lets the new owner remove other hosts after an ownership transfer', function () {
    Bus::fake();
    Mail::fake();

    $originalOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $otherHost = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($originalOwner)->create());
    $event->managers()->attach([$newOwner->id, $otherHost->id]);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $originalOwner->id,
    ]);
    TransferEventOwnership::run($transfer, $newOwner);

    mockJwtAuthentication($newOwner);

    foreach ([$otherHost, $originalOwner] as $host) {
        $this->withHeaders(jwtAuthenticationHeaders())
            ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$host->id}")
            ->assertOk();
    }

    expect($event->fresh()->user_id)->toBe($newOwner->id)
        ->and($event->fresh()->managers()->pluck('users.id')->all())->toEqual([]);
});

it('forbids the previous owner from removing hosts after an ownership transfer', function () {
    Bus::fake();
    Mail::fake();

    $originalOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $otherHost = User::factory()->create();
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->for($originalOwner)->create());
    $event->managers()->attach([$newOwner->id, $otherHost->id]);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $originalOwner->id,
    ]);
    TransferEventOwnership::run($transfer, $newOwner);

    mockJwtAuthentication($originalOwner);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->deleteJson("/api/v1/host/events/{$event->id}/managers/{$otherHost->id}")
        ->assertForbidden();
});
