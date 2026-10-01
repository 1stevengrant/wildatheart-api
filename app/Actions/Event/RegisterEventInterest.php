<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Carbon;
use App\Models\EventUserInterest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Data\V1\RegisterEventInterestData;

class RegisterEventInterest
{
    use AsAction;

    public function handle(User $user, string $eventId, ?RegisterEventInterestData $profile = null): EventUserInterest
    {
        $event = Event::with(['user', 'managers', 'eventType'])->findOrFail($eventId);

        if ($profile) {
            $user->fill($profile->profileAttributes())->save();
        }

        $existing = $user->interestIn($eventId);

        if ($existing) {
            return $existing;
        }

        $now = Carbon::now();
        $user->interestedEvents()->newPivotStatement()->insert([
            'user_id' => $user->id,
            'event_id' => $eventId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DispatchEventInterestNotifications::run($event, $user);

        return EventUserInterest::query()
            ->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->firstOrFail();
    }
}
