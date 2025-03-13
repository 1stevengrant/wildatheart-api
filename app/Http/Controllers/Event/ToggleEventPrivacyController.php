<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleEventPrivacyRequest;

class ToggleEventPrivacyController extends Controller
{
    public function __invoke(Event $event, ToggleEventPrivacyRequest $request)
    {
        $event->togglePrivacy();

        $eventPrivacy = $event->is_private ? 'private' : 'public';

        dispatch(new LogActivityJob(
            description: 'Toggle Event Privacy',
            causedBy: auth()->user(),
            performedOn: $event
        ));

        return redirect()->back()->with(['notifications' => [
            'message' => 'Event is now ' . $eventPrivacy,
            'type' => 'success',
        ],
        ]);
    }
}
