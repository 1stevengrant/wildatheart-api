<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\WeGive\TagDonorJob;

use function Pest\Laravel\actingAs;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Jobs\WeGive\FindOrCreateDonorJob;

beforeEach(function () {
    Mail::fake();
    Bus::fake();
});

it('dispatches WeGive tagging jobs when user accepts host invitation', function () {
    $eventOwner = User::factory()->create(['email' => 'owner@example.com']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $event = Event::factory()->fires()->approved()->for($eventOwner)->create();
    $event->load('eventType'); // Explicitly load relationship

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $invitedUser->email,
        'invited_by_user_id' => $eventOwner->id,
    ]);

    actingAs($invitedUser)
        ->post(route('accept.host.invitation.confirm', $invitation))
        ->assertRedirect(route('my.events.edit', $event));

    // Assert WeGive job chain was dispatched
    Bus::assertDispatched(FindOrCreateDonorJob::class, function ($job) use ($invitedUser, $event) {
        if (count($job->chained ?? []) === 0) {
            return false;
        }

        $chainedJob = unserialize($job->chained[0]); // Unserialize the chained job

        return $job->donorModel->id === $invitedUser->id
            && count($job->chained) === 1
            && $chainedJob instanceof TagDonorJob
            && $chainedJob->taggable->id === $invitedUser->id
            && $chainedJob->weGiveTagId == $event->eventType->wegive_tag; // Use == for type-safe comparison
    });
});

it('attaches user as manager when accepting host invitation', function () {
    $eventOwner = User::factory()->create(['email' => 'owner@example.com']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $event = Event::factory()->fires()->approved()->for($eventOwner)->create();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $invitedUser->email,
        'invited_by_user_id' => $eventOwner->id,
    ]);

    actingAs($invitedUser)
        ->post(route('accept.host.invitation.confirm', $invitation));

    expect($event->fresh()->managers->contains($invitedUser))->toBeTrue();
});

it('marks invitation as accepted when user accepts', function () {
    $eventOwner = User::factory()->create(['email' => 'owner@example.com']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $event = Event::factory()->fires()->approved()->for($eventOwner)->create();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $invitedUser->email,
        'invited_by_user_id' => $eventOwner->id,
    ]);

    expect($invitation->isPending())->toBeTrue();

    actingAs($invitedUser)
        ->post(route('accept.host.invitation.confirm', $invitation));

    expect($invitation->fresh()->isAccepted())->toBeTrue();
});

it('does not dispatch WeGive jobs if invitation is not pending', function () {
    // Create event BEFORE faking bus to avoid capturing EventObserver jobs
    $eventOwner = User::factory()->create(['email' => 'owner@example.com']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);

    // Temporarily restore real bus
    Bus::swap(app(\Illuminate\Contracts\Bus\Dispatcher::class));
    $event = Event::factory()->fires()->approved()->for($eventOwner)->create();

    // Re-fake bus for the controller test
    Bus::fake();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $invitedUser->email,
        'invited_by_user_id' => $eventOwner->id,
        'accepted_at' => now(),
    ]);

    actingAs($invitedUser)
        ->post(route('accept.host.invitation.confirm', $invitation));

    Bus::assertNotDispatched(FindOrCreateDonorJob::class);
    Bus::assertNotDispatched(TagDonorJob::class);
});

it('rejects invitation if email does not match logged in user', function () {
    $eventOwner = User::factory()->create(['email' => 'owner@example.com']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $differentUser = User::factory()->create(['email' => 'different@example.com']);

    // Temporarily restore real bus
    Bus::swap(app(\Illuminate\Contracts\Bus\Dispatcher::class));
    $event = Event::factory()->fires()->approved()->for($eventOwner)->create();

    // Re-fake bus for the controller test
    Bus::fake();

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $invitedUser->email,
        'invited_by_user_id' => $eventOwner->id,
    ]);

    actingAs($differentUser)
        ->post(route('accept.host.invitation.confirm', $invitation))
        ->assertRedirect(route('home'));

    expect($event->fresh()->managers->contains($differentUser))->toBeFalse();
    Bus::assertNotDispatched(FindOrCreateDonorJob::class);
    Bus::assertNotDispatched(TagDonorJob::class);
});

it('uses event type wegive tag when tagging new co-host', function () {
    $eventOwner = User::factory()->create();
    $invitedUser = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($eventOwner)->create();
    $event->load('eventType'); // Explicitly load relationship

    $invitation = EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $invitedUser->email,
        'invited_by_user_id' => $eventOwner->id,
    ]);

    actingAs($invitedUser)
        ->post(route('accept.host.invitation.confirm', $invitation));

    Bus::assertDispatched(FindOrCreateDonorJob::class, function ($job) use ($event) {
        if (! isset($job->chained[0])) {
            return false;
        }

        $chainedJob = unserialize($job->chained[0]); // Unserialize the chained job

        return $chainedJob instanceof TagDonorJob
            && $chainedJob->weGiveTagId == $event->eventType->wegive_tag; // Use == for type-safe comparison
    });
});
