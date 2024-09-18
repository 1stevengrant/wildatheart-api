<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
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

        activity()
            ->performedOn($event)
            ->causedBy(auth()->user())
            ->log('Toggle Event Approved At');

        return to_route('my.events.active')->with(['notifications' => [
            'message' => 'Event updated successfully!',
            'type' => 'success',
        ]]);
    }
}
