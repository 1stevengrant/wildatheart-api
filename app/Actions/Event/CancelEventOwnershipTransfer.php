<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\EventOwnershipTransfer;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelEventOwnershipTransfer
{
    use AsAction;

    public function handle(Event $event, User $actor): ?EventOwnershipTransfer
    {
        return DB::transaction(function () use ($event, $actor): ?EventOwnershipTransfer {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('transferOwnership', $event);

            $transfer = EventOwnershipTransfer::where('event_id', $event->id)->first();

            if (! $transfer) {
                return null;
            }

            $transfer->delete();

            return $transfer;
        });
    }
}
