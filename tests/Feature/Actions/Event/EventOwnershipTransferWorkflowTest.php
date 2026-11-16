<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Mail\OwnershipTransferCompletedMail;
use App\Mail\OwnershipTransferInvitationMail;
use App\Actions\Event\AcceptEventOwnershipTransfer;
use App\Actions\Event\CancelEventOwnershipTransfer;
use App\Exceptions\EventOwnershipTransferException;
use App\Actions\Event\InitiateEventOwnershipTransfer;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->coHost = User::factory()->create();
    $this->event = Event::factory()->fires()->approved()->for($this->owner)->create();
    $this->event->managers()->attach($this->coHost->id);

    Mail::fake();
    Bus::fake();
});

function transferEvent(Event $event, User $recipient, bool $accepted = false): EventOwnershipTransfer
{
    return EventOwnershipTransfer::create([
        'event_id' => $event->id,
        'email' => $recipient->email,
        'transferred_by_user_id' => $event->user_id,
        'accepted_at' => $accepted ? now() : null,
    ]);
}

describe('initiating a transfer', function () {
    it('creates a pending transfer to a co-host and mails them', function () {
        $result = InitiateEventOwnershipTransfer::run($this->event, $this->owner, mb_strtoupper(" {$this->coHost->email} "));

        expect($result->recipient->is($this->coHost))->toBeTrue()
            ->and($result->transfer->email)->toBe(mb_strtolower($this->coHost->email))
            ->and($result->transfer->transferred_by_user_id)->toBe($this->owner->id)
            ->and($this->event->pendingOwnershipTransfer->is($result->transfer))->toBeTrue();

        Mail::assertQueuedCount(1);
        Mail::assertQueued(OwnershipTransferInvitationMail::class, fn (OwnershipTransferInvitationMail $mail) => $mail->hasTo(mb_strtolower($this->coHost->email)));
    });

    it('allows a new transfer when only historical transfers exist', function () {
        transferEvent($this->event, $this->coHost, accepted: true);

        InitiateEventOwnershipTransfer::run($this->event, $this->owner, $this->coHost->email);

        expect(EventOwnershipTransfer::query()->where('event_id', $this->event->id)->count())->toBe(1)
            ->and(EventOwnershipTransfer::query()->withoutGlobalScope('pending')->where('event_id', $this->event->id)->count())->toBe(2);
    });

    it('only lets the owner transfer ownership', function () {
        expect(fn () => InitiateEventOwnershipTransfer::run($this->event, $this->coHost, $this->coHost->email))
            ->toThrow(EventOwnershipTransferException::class, 'Only the event owner can transfer ownership.');

        expect($this->event->pendingOwnershipTransfer)->toBeNull();
    });

    it('rejects ineligible recipients', function (Closure $email, string $message) {
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);

        expect(fn () => InitiateEventOwnershipTransfer::run($this->event, $this->owner, $email($this)))
            ->toThrow(EventOwnershipTransferException::class, $message);

        expect($this->event->pendingOwnershipTransfer)->toBeNull();

        Mail::assertNothingQueued();
    })->with([
        'an unknown email' => [fn () => 'nobody@example.com', 'User not found. Only existing hosts can be promoted to owner.'],
        'the owner' => [fn ($test) => $test->owner->email, 'You already own this event.'],
        'a user who is not a host' => [fn () => 'stranger@example.com', 'Only existing hosts can be promoted to owner. Please add them as a host first.'],
    ]);

    it('rejects a second pending transfer', function () {
        InitiateEventOwnershipTransfer::run($this->event, $this->owner, $this->coHost->email);

        expect(fn () => InitiateEventOwnershipTransfer::run($this->event, $this->owner, $this->coHost->email))
            ->toThrow(EventOwnershipTransferException::class, 'There is already a pending ownership transfer for this event.');

        expect(EventOwnershipTransfer::query()->where('event_id', $this->event->id)->count())->toBe(1);
    });
});

describe('cancelling a transfer', function () {
    it('deletes the pending transfer and keeps historical ones', function () {
        $historical = transferEvent($this->event, $this->coHost, accepted: true);
        $pending = transferEvent($this->event, $this->coHost);

        $cancelled = CancelEventOwnershipTransfer::run($this->event, $this->owner);

        expect($cancelled->is($pending))->toBeTrue()
            ->and(EventOwnershipTransfer::query()->withoutGlobalScope('pending')->whereKey($pending->id)->exists())->toBeFalse()
            ->and(EventOwnershipTransfer::query()->withoutGlobalScope('pending')->whereKey($historical->id)->exists())->toBeTrue();
    });

    it('only lets the owner cancel', function () {
        transferEvent($this->event, $this->coHost);

        expect(fn () => CancelEventOwnershipTransfer::run($this->event, $this->coHost))
            ->toThrow(EventOwnershipTransferException::class, 'Only the event owner can cancel an ownership transfer.');

        expect($this->event->pendingOwnershipTransfer)->not->toBeNull();
    });

    it('fails when there is no pending transfer', function () {
        transferEvent($this->event, $this->coHost, accepted: true);

        expect(fn () => CancelEventOwnershipTransfer::run($this->event, $this->owner))
            ->toThrow(EventOwnershipTransferException::class, 'No pending ownership transfer found.');
    });
});

describe('accepting a transfer', function () {
    beforeEach(function () {
        $this->transfer = transferEvent($this->event, $this->coHost);
    });

    it('swaps owner and host, reassigns attendees, mails both parties and tags the previous owner', function () {
        $ownerAttendee = Attendee::factory()->for($this->event)->for($this->owner, 'user')->create();

        Mail::fake();
        Bus::fake();

        $event = AcceptEventOwnershipTransfer::run($this->transfer, $this->coHost);

        expect($event->user_id)->toBe($this->coHost->id)
            ->and($event->managers->modelKeys())->toBe([$this->owner->id])
            ->and($ownerAttendee->fresh()->user_id)->toBe($this->coHost->id)
            ->and($this->transfer->isAccepted())->toBeTrue();

        Mail::assertQueuedCount(2);
        Mail::assertQueued(OwnershipTransferCompletedMail::class, fn (OwnershipTransferCompletedMail $mail) => $mail->hasTo($this->coHost->email));
        Mail::assertQueued(OwnershipTransferCompletedMail::class, fn (OwnershipTransferCompletedMail $mail) => $mail->hasTo($this->owner->email));

        Bus::assertDispatchedTimes(FindOrCreateDonorJob::class, 1);
        Bus::assertDispatched(FindOrCreateDonorJob::class, function (FindOrCreateDonorJob $job) use ($event) {
            $chainedJob = unserialize($job->chained[0]);

            return $job->donorModel->is($this->owner)
                && $chainedJob instanceof TagDonorJob
                && $chainedJob->taggable->is($this->owner)
                && $chainedJob->weGiveTagId == $event->eventType->wegive_tag;
        });
    });

    it('is idempotent when the same stale transfer is accepted twice', function () {
        $staleCopy = EventOwnershipTransfer::query()->findOrFail($this->transfer->id);

        AcceptEventOwnershipTransfer::run($this->transfer, $this->coHost);
        AcceptEventOwnershipTransfer::run($staleCopy, $this->coHost);

        $event = $this->event->fresh();

        expect($event->user_id)->toBe($this->coHost->id)
            ->and($event->managers->modelKeys())->toBe([$this->owner->id]);

        Mail::assertQueuedCount(2);
        Bus::assertDispatchedTimes(FindOrCreateDonorJob::class, 1);
    });

    it('rejects a different recipient', function () {
        $stranger = User::factory()->create();

        expect(fn () => AcceptEventOwnershipTransfer::run($this->transfer, $stranger))
            ->toThrow(EventOwnershipTransferException::class, 'This ownership transfer is for a different email address.');

        expect($this->event->fresh()->user_id)->toBe($this->owner->id)
            ->and($this->transfer->fresh()->isPending())->toBeTrue();
    });

    it('rejects acceptance when the event has been deleted', function () {
        $this->event->delete();

        expect(fn () => AcceptEventOwnershipTransfer::run($this->transfer->fresh(), $this->coHost))
            ->toThrow(EventOwnershipTransferException::class, 'This event is no longer available.');

        expect($this->transfer->fresh()->isPending())->toBeTrue();
    });
});
