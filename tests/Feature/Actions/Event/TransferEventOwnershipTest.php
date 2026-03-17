<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Actions\Event\TransferEventOwnership;

beforeEach(function () {
    Mail::fake();
    Bus::fake();
});

it('dispatches WeGive tagging jobs when ownership is transferred', function () {
    $oldOwner = User::factory()->create(['email' => 'oldowner@example.com']);
    $newOwner = User::factory()->create(['email' => 'newowner@example.com']);
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();
    $event->load('eventType'); // Explicitly load relationship

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    // Assert WeGive job chain was dispatched for the old owner (now co-host)
    Bus::assertDispatched(FindOrCreateDonorJob::class, function ($job) use ($oldOwner, $event) {
        if (count($job->chained ?? []) === 0) {
            return false;
        }

        $chainedJob = unserialize($job->chained[0]); // Unserialize the chained job

        return $job->donorModel->id === $oldOwner->id
            && count($job->chained) === 1
            && $chainedJob instanceof TagDonorJob
            && $chainedJob->taggable->id === $oldOwner->id
            && $chainedJob->weGiveTagId == $event->eventType->wegive_tag; // Use == for type-safe comparison
    });
});

it('transfers ownership to new owner', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    expect($event->fresh()->user_id)->toBe($newOwner->id);
});

it('adds old owner as co-host after transfer', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    expect($event->fresh()->managers->contains($oldOwner))->toBeTrue();
});

it('removes new owner from managers if they were a co-host', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();

    // Add new owner as a manager first
    $event->managers()->attach($newOwner->id);

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    // New owner should not be in managers anymore (they're now the owner)
    expect($event->fresh()->managers->contains($newOwner))->toBeFalse();
});

it('marks transfer as accepted', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    expect($transfer->isPending())->toBeTrue();

    TransferEventOwnership::run($transfer, $newOwner);

    expect($transfer->fresh()->isAccepted())->toBeTrue();
});

it('updates attendee records from old owner to new owner', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();

    // Create attendee record for old owner
    $attendee = Attendee::factory()->for($event)->for($oldOwner, 'user')->create();

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    expect($attendee->fresh()->user_id)->toBe($newOwner->id);
});

it('uses event type wegive tag when tagging old owner as co-host', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();
    $event->load('eventType'); // Explicitly load relationship

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    Bus::assertDispatched(FindOrCreateDonorJob::class, function ($job) use ($event) {
        if (! isset($job->chained[0])) {
            return false;
        }

        $chainedJob = unserialize($job->chained[0]); // Unserialize the chained job

        return $chainedJob instanceof TagDonorJob
            && $chainedJob->weGiveTagId == $event->eventType->wegive_tag; // Use == for type-safe comparison
    });
});

it('tags old owner with correct user instance', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $event = Event::factory()->fires()->approved()->for($oldOwner)->create();
    $event->load('eventType'); // Explicitly load relationship

    $transfer = EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $newOwner->email,
        'transferred_by_user_id' => $oldOwner->id,
    ]);

    TransferEventOwnership::run($transfer, $newOwner);

    Bus::assertDispatched(FindOrCreateDonorJob::class, function ($job) use ($oldOwner) {
        if (! isset($job->chained[0])) {
            return false;
        }

        $chainedJob = unserialize($job->chained[0]); // Unserialize the chained job

        return $job->donorModel->id === $oldOwner->id
            && $chainedJob instanceof TagDonorJob
            && $chainedJob->taggable->id === $oldOwner->id;
    });
});
