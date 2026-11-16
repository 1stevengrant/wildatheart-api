<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Attendee;
use Illuminate\Support\Facades\DB;
use App\Models\EventOwnershipTransfer;
use Lorisleiva\Actions\Concerns\AsAction;

class TransferEventOwnership
{
    use AsAction;

    /**
     * Swaps the owner and the new owner's host role, moves the owner's attendee
     * records and marks the transfer accepted. Returns the previous owner.
     */
    public function handle(EventOwnershipTransfer $transfer, User $newOwner): User
    {
        return DB::transaction(function () use ($transfer, $newOwner): User {
            $event = $transfer->event;

            $previousOwner = $event->user;

            $event->managers()->detach($newOwner->id);

            $event->user()->associate($newOwner);
            $event->save();

            Attendee::query()
                ->where('event_id', $event->id)
                ->where('user_id', $previousOwner->id)
                ->update(['user_id' => $newOwner->id]);

            $event->managers()->syncWithoutDetaching([$previousOwner->id]);

            $transfer->markAsAccepted();

            return $previousOwner;
        });
    }
}
