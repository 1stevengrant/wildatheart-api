<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Jobs\LogActivityJob;
use App\Models\Event;
use Illuminate\Http\Request;

class ToggleEventApprovedAtController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        abort_unless($event->user_id == auth()->user()->id, 403);

        $event->update([
            'approved_at' => $event->approved_at ? null : now(),
        ]);

        dispatch(new LogActivityJob(
            description: 'Toggle Event Approved At',
            causedBy: auth()->user(),
            performedOn: $event
        ));

        return to_route('my.events.active')->with(['notifications' => [
            'message' => 'Event updated successfully!',
            'type' => 'success',
        ]]);
    }
}
