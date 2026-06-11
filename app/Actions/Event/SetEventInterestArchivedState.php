<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Carbon;
use App\Models\EventUserInterest;
use Lorisleiva\Actions\Concerns\AsAction;

class SetEventInterestArchivedState
{
    use AsAction;

    public function handle(User $host, Event $event, User $contact, ?Carbon $archivedAt): ?EventUserInterest
    {
        if (! $event->isOwnedOrManagedBy($host)) {
            return null;
        }

        $interestQuery = EventUserInterest::query()
            ->where('event_id', $event->id)
            ->where('user_id', $contact->id);

        if (! $interestQuery->exists()) {
            return null;
        }

        $interestQuery->update([
            'archived_at' => $archivedAt,
            'updated_at' => Carbon::now(),
        ]);

        return EventUserInterest::query()
            ->where('event_id', $event->id)
            ->where('user_id', $contact->id)
            ->with(['user', 'event'])
            ->first();
    }
}
