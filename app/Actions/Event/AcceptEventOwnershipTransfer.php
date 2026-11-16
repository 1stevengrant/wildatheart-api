<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\OwnershipTransferCompletedMail;
use App\Exceptions\EventOwnershipTransferException;

class AcceptEventOwnershipTransfer
{
    use AsAction;

    /**
     * Accepting a transfer that was already accepted is a no-op.
     *
     * @throws EventOwnershipTransferException
     */
    public function handle(EventOwnershipTransfer $transfer, User $recipient): Event
    {
        if ($recipient->email !== $transfer->email) {
            throw EventOwnershipTransferException::wrongRecipient();
        }

        $event = $transfer->event;

        if ($event === null) {
            throw EventOwnershipTransferException::eventUnavailable();
        }

        $previousOwner = DB::transaction(function () use ($event, $transfer, $recipient): ?User {
            $lockedEvent = Event::query()->lockForUpdate()->find($event->getKey());

            if ($lockedEvent === null) {
                throw EventOwnershipTransferException::eventUnavailable();
            }

            $lockedTransfer = EventOwnershipTransfer::query()
                ->withoutGlobalScope('pending')
                ->lockForUpdate()
                ->findOrFail($transfer->getKey());

            if ($lockedTransfer->isAccepted()) {
                return null;
            }

            if ($recipient->id === $lockedEvent->user_id) {
                $lockedTransfer->markAsAccepted();

                return null;
            }

            $lockedTransfer->setRelation('event', $lockedEvent);

            return TransferEventOwnership::run($lockedTransfer, $recipient);
        });

        $transfer->refresh();
        $event->refresh();

        if (! $previousOwner) {
            return $event;
        }

        Mail::to($recipient->email)->send(new OwnershipTransferCompletedMail($event, $recipient, $previousOwner, true));

        Mail::to($previousOwner->email)->send(new OwnershipTransferCompletedMail($event, $recipient, $previousOwner, false));

        Bus::chain([
            new FindOrCreateDonorJob(
                donorModel: $previousOwner,
            ),
            new TagDonorJob(
                taggable: $previousOwner,
                weGiveTagId: $event->eventType->wegive_tag,
            ),
        ])->dispatch();

        return $event;
    }
}
