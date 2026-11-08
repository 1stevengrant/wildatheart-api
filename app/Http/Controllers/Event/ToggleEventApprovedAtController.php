<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Http\Controllers\Controller;
use App\Actions\Event\CatchUpEventSchedule;
use App\Http\Requests\ToggleEventApprovedAtRequest;

class ToggleEventApprovedAtController extends Controller
{
    public function __invoke(ToggleEventApprovedAtRequest $request, Event $event)
    {
        $event->toggleApprovedAt();

        if ($event->approved_at !== null) {
            CatchUpEventSchedule::run($event);
        }

        LogActivityJob::dispatch(
            description: 'Toggle Event Approved At',
            causedBy: auth()->user(),
            performedOn: $event
        );

        return to_route('my.events.active')->with([
            'notifications' => [
                'message' => 'Event updated successfully!',
                'type' => 'success',
            ],
        ]);
    }
}
