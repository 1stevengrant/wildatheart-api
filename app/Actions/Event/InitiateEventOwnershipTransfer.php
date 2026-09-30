<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\EventOwnershipTransfer;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Validation\ValidationException;

class InitiateEventOwnershipTransfer
{
    use AsAction;

    /**
     * Creates the pending transfer. The caller sends the invitation email, since the legacy
     * dashboard and the v1 API link to different acceptance pages.
     */
    public function handle(Event $event, User $newOwner, User $actor): EventOwnershipTransfer
    {
        return DB::transaction(function () use ($event, $newOwner, $actor): EventOwnershipTransfer {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            Gate::forUser($actor)->authorize('transferOwnership', $event);

            if ($newOwner->id === $event->user_id) {
                throw ValidationException::withMessages(['user_id' => 'You already own this event.']);
            }

            if (! $event->managers()->whereKey($newOwner->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'Only existing hosts can be promoted to owner. Please add them as a host first.',
                ]);
            }

            if (EventOwnershipTransfer::where('event_id', $event->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'There is already a pending ownership transfer for this event.',
                ]);
            }

            return EventOwnershipTransfer::create([
                'event_id' => $event->id,
                'email' => $newOwner->email,
                'transferred_by_user_id' => $actor->id,
            ]);
        });
    }
}
