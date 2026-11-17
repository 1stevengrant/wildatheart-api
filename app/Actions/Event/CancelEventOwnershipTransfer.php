<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use App\Models\EventOwnershipTransfer;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Exceptions\EventOwnershipTransferException;

class CancelEventOwnershipTransfer
{
    use AsAction;

    /** @throws EventOwnershipTransferException */
    public function handle(Event $event, User $owner): EventOwnershipTransfer
    {
        return DB::transaction(function () use ($event, $owner): EventOwnershipTransfer {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            if ($owner->cannot('transferOwnership', $lockedEvent)) {
                throw EventOwnershipTransferException::onlyOwnerCanCancel();
            }

            $transfer = $lockedEvent->pendingOwnershipTransfer()->lockForUpdate()->first();

            if (! $transfer) {
                throw EventOwnershipTransferException::noPendingTransfer();
            }

            $transfer->delete();

            return $transfer;
        });
    }
}
