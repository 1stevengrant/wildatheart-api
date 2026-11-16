<?php

use App\Models\User;
use App\Models\Event;
use App\Mail\HostAddedMail;
use App\Mail\HostRemovedMail;
use App\Jobs\WeGive\TagDonorJob;
use App\Mail\HostInvitationMail;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Actions\Event\InviteEventManager;
use App\Actions\Event\RemoveEventManager;
use App\Exceptions\EventManagerException;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Actions\Event\SyncCoHostInvitations;
use App\Actions\Event\AcceptEventManagerInvitation;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->coHost = User::factory()->create();
    $this->event = Event::factory()->fires()->approved()->for($this->owner)->create();
    $this->event->managers()->attach($this->coHost->id);

    Mail::fake();
    Bus::fake();
});

function inviteToEvent(Event $event, string $email): EventManagerInvitation
{
    return EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => $email,
        'invited_by_user_id' => $event->user_id,
    ]);
}

describe('inviting a manager', function () {
    it('creates a pending invitation for a normalised email and mails it', function () {
        $result = InviteEventManager::run($this->event, $this->coHost, '  New.Host@Example.com ');

        expect($result->invitation->email)->toBe('new.host@example.com')
            ->and($result->invitation->invited_by_user_id)->toBe($this->coHost->id)
            ->and($result->invitation->isPending())->toBeTrue()
            ->and($result->invitee)->toBeNull()
            ->and($result->message())->toBe('An invitation has been sent to new.host@example.com. They will become a host once they create an account and accept.');

        Mail::assertQueued(HostInvitationMail::class, fn (HostInvitationMail $mail) => $mail->hasTo('new.host@example.com'));
    });

    it('names an existing user in the result message', function () {
        $invitee = User::factory()->create();

        $result = InviteEventManager::run($this->event, $this->owner, $invitee->email);

        expect($result->invitee->is($invitee))->toBeTrue()
            ->and($result->message())->toBe("An invitation has been sent to {$invitee->fullname}. They will become a host once they accept.");
    });

    it('rejects ineligible candidates without creating an invitation', function (Closure $email, string $message) {
        inviteToEvent($this->event, 'pending@example.com');

        expect(fn () => InviteEventManager::run($this->event, $this->owner, $email($this)))
            ->toThrow(EventManagerException::class, $message);

        expect(EventManagerInvitation::query()->where('event_id', $this->event->id)->count())->toBe(1);

        Mail::assertNothingQueued();
    })->with([
        'the owner' => [fn ($test) => $test->owner->email, 'This user is already the event owner.'],
        'an existing manager' => [fn ($test) => $test->coHost->email, 'This user is already a host for this event.'],
        'an email with a pending invitation' => [fn () => 'PENDING@example.com', 'An invitation has already been sent to this email address.'],
    ]);
});

describe('re-inviting a removed host', function () {
    beforeEach(function () {
        $this->invitee = User::factory()->create();
        $invitation = inviteToEvent($this->event, $this->invitee->email);

        AcceptEventManagerInvitation::run($invitation, $this->invitee);
        RemoveEventManager::run($this->event, $this->owner, (string) $this->invitee->id);

        Mail::fake();
    });

    it('replaces the accepted invitation with a fresh pending one', function () {
        $result = InviteEventManager::run($this->event, $this->owner, $this->invitee->email);

        $invitations = EventManagerInvitation::query()
            ->withoutGlobalScope('pending')
            ->where('event_id', $this->event->id)
            ->get();

        expect($invitations->modelKeys())->toBe([$result->invitation->id])
            ->and($result->invitation->isPending())->toBeTrue();

        Mail::assertQueued(HostInvitationMail::class, fn (HostInvitationMail $mail) => $mail->hasTo($this->invitee->email));
    });

    it('lets the host accept the new invitation', function () {
        $result = InviteEventManager::run($this->event, $this->owner, $this->invitee->email);

        AcceptEventManagerInvitation::run($result->invitation, $this->invitee);

        expect($this->event->fresh()->managers->contains($this->invitee))->toBeTrue();
    });

    it('replaces the accepted invitation when co-hosts are synced from the event form', function () {
        SyncCoHostInvitations::run($this->event, [$this->invitee->email], $this->owner);

        expect($this->event->pendingManagerInvitations()->pluck('email')->all())->toBe([$this->invitee->email])
            ->and(EventManagerInvitation::query()->withoutGlobalScope('pending')->where('event_id', $this->event->id)->count())->toBe(1);
    });

    it('keeps accepted invitations for other events', function () {
        $otherEvent = Event::factory()->fires()->approved()->for($this->owner)->create();
        $otherInvitation = inviteToEvent($otherEvent, $this->invitee->email);
        AcceptEventManagerInvitation::run($otherInvitation, $this->invitee);

        InviteEventManager::run($this->event, $this->owner, $this->invitee->email);

        expect($otherInvitation->fresh()->isAccepted())->toBeTrue();
    });
});

describe('removing a manager', function () {
    it('cancels a pending invitation before looking for a host', function () {
        $invitation = inviteToEvent($this->event, 'pending@example.com');

        $result = RemoveEventManager::run($this->event, $this->owner, $invitation->id);

        expect($result->cancelledInvitation->is($invitation))->toBeTrue()
            ->and($result->message())->toBe('Invitation to pending@example.com has been cancelled.')
            ->and(EventManagerInvitation::query()->whereKey($invitation->id)->exists())->toBeFalse();

        Mail::assertNothingQueued();
    });

    it('does not cancel another event invitation', function () {
        $otherEvent = Event::factory()->fires()->approved()->create();
        $invitation = inviteToEvent($otherEvent, 'pending@example.com');

        expect(fn () => RemoveEventManager::run($this->event, $this->owner, $invitation->id))
            ->toThrow(EventManagerException::class, 'Host or invitation not found.');

        expect($invitation->fresh())->not->toBeNull();
    });

    it('removes a host and mails only the removed host when the owner removes them', function () {
        $result = RemoveEventManager::run($this->event, $this->owner, (string) $this->coHost->id);

        expect($result->removedManager->is($this->coHost))->toBeTrue()
            ->and($result->message())->toBe("{$this->coHost->fullname} has been removed as a host.")
            ->and($this->event->fresh()->managers)->toBeEmpty();

        Mail::assertQueuedCount(1);
        Mail::assertQueued(HostRemovedMail::class, fn (HostRemovedMail $mail) => $mail->hasTo($this->coHost->email));
    });

    it('also mails the owner when a co-host removes a host', function () {
        $otherCoHost = User::factory()->create();
        $this->event->managers()->attach($otherCoHost->id);

        RemoveEventManager::run($this->event, $this->coHost, (string) $otherCoHost->id);

        Mail::assertQueuedCount(2);
        Mail::assertQueued(HostRemovedMail::class, fn (HostRemovedMail $mail) => $mail->hasTo($otherCoHost->email));
        Mail::assertQueued(HostRemovedMail::class, fn (HostRemovedMail $mail) => $mail->hasTo($this->owner->email));
    });

    it('rejects ids that are not a removable host', function (Closure $managerId, string $message) {
        $stranger = User::factory()->create();

        expect(fn () => RemoveEventManager::run($this->event, $this->owner, $managerId($this, $stranger)))
            ->toThrow(EventManagerException::class, $message);

        expect($this->event->fresh()->managers->contains($this->coHost))->toBeTrue();

        Mail::assertNothingQueued();
    })->with([
        'the owner' => [fn ($test) => (string) $test->owner->id, 'Cannot remove the event owner.'],
        'a user who is not a host' => [fn ($test, $stranger) => (string) $stranger->id, 'This user is not a host for this event.'],
        'an unknown id' => [fn () => '01JUNKNOWNINVITATION0000000', 'Host or invitation not found.'],
        'a stale invitation id that MySQL would cast to a host id' => [fn ($test) => "{$test->coHost->id}JSTALEULID", 'Host or invitation not found.'],
    ]);
});

describe('accepting an invitation', function () {
    beforeEach(function () {
        $this->invitee = User::factory()->create();
        $this->invitation = inviteToEvent($this->event, $this->invitee->email);

        Mail::fake();
        Bus::fake();
    });

    it('adds the recipient as a host, mails both parties and tags them in WeGive', function () {
        $event = AcceptEventManagerInvitation::run($this->invitation, $this->invitee);

        expect($event->is($this->event))->toBeTrue()
            ->and($this->invitation->isAccepted())->toBeTrue()
            ->and($this->event->fresh()->managers->contains($this->invitee))->toBeTrue();

        Mail::assertQueuedCount(2);
        Mail::assertQueued(HostAddedMail::class, fn (HostAddedMail $mail) => $mail->hasTo($this->invitee->email));
        Mail::assertQueued(HostAddedMail::class, fn (HostAddedMail $mail) => $mail->hasTo($this->owner->email));

        Bus::assertDispatched(FindOrCreateDonorJob::class, function (FindOrCreateDonorJob $job) {
            $chainedJob = unserialize($job->chained[0]);

            return $job->donorModel->is($this->invitee)
                && $chainedJob instanceof TagDonorJob
                && $chainedJob->taggable->is($this->invitee)
                && $chainedJob->weGiveTagId == $this->event->eventType->wegive_tag;
        });
    });

    it('is idempotent when the same stale invitation is accepted twice', function () {
        $staleCopy = EventManagerInvitation::query()->findOrFail($this->invitation->id);

        AcceptEventManagerInvitation::run($this->invitation, $this->invitee);
        AcceptEventManagerInvitation::run($staleCopy, $this->invitee);

        expect($this->event->fresh()->managers->where('id', $this->invitee->id))->toHaveCount(1);

        Mail::assertQueuedCount(2);
        Bus::assertDispatchedTimes(FindOrCreateDonorJob::class, 1);
    });

    it('rejects a different recipient', function () {
        $stranger = User::factory()->create();

        expect(fn () => AcceptEventManagerInvitation::run($this->invitation, $stranger))
            ->toThrow(EventManagerException::class, 'This invitation is for a different email address.');

        expect($this->invitation->fresh()->isPending())->toBeTrue()
            ->and($this->event->fresh()->managers->contains($stranger))->toBeFalse();
    });

    it('rejects acceptance when the event has been deleted', function () {
        $this->event->delete();

        expect(fn () => AcceptEventManagerInvitation::run($this->invitation->fresh(), $this->invitee))
            ->toThrow(EventManagerException::class, 'This event is no longer available.');

        expect($this->invitation->fresh()->isPending())->toBeTrue();
    });

    it('accepts without side effects when the recipient is already a host', function () {
        $this->event->managers()->attach($this->invitee->id);

        AcceptEventManagerInvitation::run($this->invitation, $this->invitee);

        expect($this->invitation->isAccepted())->toBeTrue();

        Mail::assertNothingQueued();
        Bus::assertNotDispatched(FindOrCreateDonorJob::class);
    });

    it('does not make the owner a host of their own event', function () {
        $this->event->update(['user_id' => $this->invitee->id]);

        AcceptEventManagerInvitation::run($this->invitation, $this->invitee);

        expect($this->invitation->isAccepted())->toBeTrue()
            ->and($this->event->fresh()->managers->contains($this->invitee))->toBeFalse();

        Mail::assertNothingQueued();
    });
});
