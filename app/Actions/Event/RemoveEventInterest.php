<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\Event\EventInterestCancelled;

class RemoveEventInterest
{
    use AsAction;

    public function handle(User $user, string $eventId): void
    {
        $interestQuery = $user->interestedEvents()->newPivotStatement()
            ->where('user_id', $user->id)
            ->where('event_id', $eventId);

        if (! $interestQuery->exists()) {
            return;
        }

        $event = Event::with(['user', 'managers', 'eventType'])->find($eventId);
        $deletedInterests = $interestQuery->delete();

        if ($deletedInterests === 0 || ! $event) {
            return;
        }

        $hostMail = Mail::to($event->user->email);

        if ($event->managers->isNotEmpty()) {
            $hostMail->cc($event->managers->pluck('email')->toArray());
        }

        $hostMail->send(new EventInterestCancelled(
            event: $event,
            user: $user,
        ));
    }
}
