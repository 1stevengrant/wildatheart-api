<?php

namespace App\Actions\Event;

use App\Jobs\LogActivityJob;
use App\Models\Event;
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
        dispatch(new LogActivityJob(
            description: 'Event approved',
            performedOn: $event,
        ));
    }
}
