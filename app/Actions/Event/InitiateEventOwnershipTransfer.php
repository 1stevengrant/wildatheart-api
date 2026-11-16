<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\OwnershipTransferInvitationMail;
use App\Exceptions\EventOwnershipTransferException;
use App\DataTransferObjects\EventOwnershipTransferInitiated;

class InitiateEventOwnershipTransfer
{
    use AsAction;

    /** @throws EventOwnershipTransferException */
    public function handle(Event $event, User $owner, string $email): EventOwnershipTransferInitiated
    {
        $email = mb_strtolower(mb_trim($email));

        $result = DB::transaction(function () use ($event, $owner, $email): EventOwnershipTransferInitiated {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            if ($owner->cannot('transferOwnership', $lockedEvent)) {
                throw EventOwnershipTransferException::onlyOwnerCanTransfer();
            }

            $recipient = User::query()->where('email', $email)->first();

            if (! $recipient) {
                throw EventOwnershipTransferException::recipientNotFound();
            }

            if ($recipient->id === $lockedEvent->user_id) {
                throw EventOwnershipTransferException::recipientIsOwner();
            }

            if (! $lockedEvent->managers()->where('user_id', $recipient->id)->exists()) {
                throw EventOwnershipTransferException::recipientIsNotManager();
            }

            if ($lockedEvent->pendingOwnershipTransfer()->exists()) {
                throw EventOwnershipTransferException::alreadyPending();
            }

            $transfer = EventOwnershipTransfer::create([
                'event_id' => $lockedEvent->id,
                'email' => $email,
                'transferred_by_user_id' => $owner->id,
            ]);

            return new EventOwnershipTransferInitiated($transfer, $recipient);
        });

        Mail::to($email)->send(new OwnershipTransferInvitationMail($event, $owner->fullname, $result->transfer, true));

        return $result;
    }
}
