<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Mail\HostAddedMail;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Exceptions\EventManagerException;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptEventManagerInvitation
{
    use AsAction;

    /**
     * Accepting an invitation that was already accepted is a no-op.
     *
     * @throws EventManagerException
     */
    public function handle(EventManagerInvitation $invitation, User $recipient): Event
    {
        if ($recipient->email !== $invitation->email) {
            throw EventManagerException::wrongRecipient();
        }

        $event = $invitation->event;

        if ($event === null) {
            throw EventManagerException::eventUnavailable();
        }

        $becameManager = DB::transaction(function () use ($event, $invitation, $recipient): bool {
            $lockedEvent = Event::query()->lockForUpdate()->find($event->getKey());

            if ($lockedEvent === null) {
                throw EventManagerException::eventUnavailable();
            }

            $lockedInvitation = EventManagerInvitation::query()
                ->withoutGlobalScope('pending')
                ->lockForUpdate()
                ->findOrFail($invitation->getKey());

            if ($lockedInvitation->isAccepted()) {
                return false;
            }

            $lockedInvitation->markAsAccepted();

            if ($recipient->id === $lockedEvent->user_id) {
                return false;
            }

            $changes = $lockedEvent->managers()->syncWithoutDetaching([$recipient->id]);

            return $changes['attached'] !== [];
        });

        $invitation->refresh();
        $event->refresh();

        if (! $becameManager) {
            return $event;
        }

        Mail::to($recipient->email)->send(new HostAddedMail($event, $recipient, $event->user));

        Mail::to($event->user->email)->send(new HostAddedMail($event, $recipient, $event->user));

        Bus::chain([
            new FindOrCreateDonorJob(
                donorModel: $recipient,
            ),
            new TagDonorJob(
                taggable: $recipient,
                weGiveTagId: $event->eventType->wegive_tag,
            ),
        ])->dispatch();

        return $event;
    }
}
