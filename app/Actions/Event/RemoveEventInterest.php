<?php

namespace App\Actions\Event;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveEventInterest
{
    use AsAction;

    public function handle(User $user, string $eventId): void
    {
        $user->interestedEvents()->newPivotStatement()
            ->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->delete();
    }
}
