<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmFireIsBurning
{
    use AsAction;

    public function handle(Event $fire, User $causer): void
    {
        $confirmed = DB::transaction(function () use ($fire): bool {
            $lockedFire = Event::query()->lockForUpdate()->findOrFail($fire->getKey());

            if ($lockedFire->hasConfirmedLatestCheckin()) {
                return false;
            }

            $now = now();

            $lockedFire->update([
                'last_checkin_confirmed_at' => $now,
                'last_checkin_email_sent_at' => $now,
                'last_checkin_reminder_sent_at' => null,
                'auto_unpublished' => false,
                'auto_unpublished_at' => null,
                'approved_at' => $now,
            ]);

            return true;
        });

        $fire->refresh();

        if (! $confirmed) {
            return;
        }

        LogActivityJob::dispatch(
            description: 'Fire published',
            causedBy: $causer,
            performedOn: $fire,
        );
    }
}
