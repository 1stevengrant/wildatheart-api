<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Attendee;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\OwnershipTransferCompletedMail;

class TransferEventOwnership
{
    use AsAction;

    public function handle(
        EventOwnershipTransfer $transfer,
        User $newOwner
    ): void {
        // Store the old owner before transferring
        $oldOwnerId = $transfer->event->user_id;
        $oldOwner = User::find($oldOwnerId);

        // Remove new owner from hosts if they were one (owner can't also be a host)
        $transfer->event->managers()->detach($newOwner->id);

        // Transfer ownership to the new user
        $transfer->event->user_id = $newOwner->id;
        $transfer->event->save();

        // Update attendee records from old owner to new owner
        Attendee::where('event_id', $transfer->event_id)
            ->where('user_id', $oldOwnerId)
            ->update(['user_id' => $newOwner->id]);

        // Add old owner as host (they become a host after transferring ownership)
        $transfer->event->managers()->syncWithoutDetaching([$oldOwnerId]);

        $event = $transfer->event;

        $transfer->markAsAccepted();

        Mail::to($newOwner->email)->send(new OwnershipTransferCompletedMail($event, $newOwner, $oldOwner, true));

        Mail::to($oldOwner->email)->send(new OwnershipTransferCompletedMail($event, $newOwner, $oldOwner, false));

        // Tag the old owner (now co-host) in WeGive
        Bus::chain([
            new FindOrCreateDonorJob(
                donorModel: $oldOwner,
            ),
            new TagDonorJob(
                taggable: $oldOwner,
                weGiveTagId: $event->eventType->wegive_tag,
            ),
        ])->dispatch();
    }
}
