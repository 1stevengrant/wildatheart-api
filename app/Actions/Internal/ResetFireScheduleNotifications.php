<?php

namespace App\Actions\Internal;

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Staging only. Forgets which schedule emails a Fire has had, so the same scenario can
 * be run again, and republishes it if the schedule check unpublished it.
 */
class ResetFireScheduleNotifications
{
    use AsAction;

    public function handle(Event $event): void
    {
        DB::transaction(function () use ($event) {
            $event->scheduleNotifications()->delete();

            if (! $event->auto_unpublished) {
                return;
            }

            $event->update([
                'approved_at' => now(),
                'auto_unpublished' => false,
                'auto_unpublished_at' => null,
            ]);
        });
    }
}
