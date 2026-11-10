<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use Lorisleiva\Actions\Concerns\AsAction;

class ApproveEvent
{
    use AsAction;

    public function handle(
        Event $event
    ): void {
        $event->update([
            'approved_at' => now(),
        ]);

        CatchUpEventSchedule::run($event);

        LogActivityJob::dispatch(
            description: 'Event approved',
            performedOn: $event,
        );
    }
}
