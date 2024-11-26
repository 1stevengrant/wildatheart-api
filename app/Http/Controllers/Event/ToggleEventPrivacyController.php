<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Jobs\LogActivityJob;
use App\Models\Event;
use Illuminate\Http\Request;

class ToggleEventPrivacyController extends Controller
{
    public function __invoke(Event $event, Request $request)
    {
        abort_unless($event->user_id == auth()->user()->id, 403);

        $event->update([
            'is_private' => ! $event->is_private,
        ]);

        $eventPrivacy = $event->is_private ? 'private' : 'public';

        dispatch(new LogActivityJob(
            description: 'Toggle Event Privacy',
            causedBy: auth()->user(),
            performedOn: $event
        ));

        return redirect()->back()->with(['notifications' => [
            'message' => 'Event privacy updated',
            'type' => 'success',
        ],
        ]);
    }
}
