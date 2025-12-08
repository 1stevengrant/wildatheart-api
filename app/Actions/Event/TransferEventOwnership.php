<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Attendee;
use Illuminate\Support\Facades\Mail;
use App\Models\EventOwnershipTransfer;
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

        // Store event reference before deleting the transfer
        $event = $transfer->event;

        // Delete transfer after successful completion
        $transfer->delete();

        // Send notification to the new owner
        Mail::to($newOwner->email)->send(new OwnershipTransferCompletedMail($event, $newOwner, $oldOwner, true));

        // Send notification to the previous owner
        Mail::to($oldOwner->email)->send(new OwnershipTransferCompletedMail($event, $newOwner, $oldOwner, false));
    }
}
