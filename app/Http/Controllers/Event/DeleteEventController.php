<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class DeleteEventController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        if ($event->user_id != auth()->user()->id) {
            return redirect()->back()->with(['notifications' => [
                'message' => 'You are not authorized to delete this event.',
                'type' => 'error',
            ],
            ]);
        }

        $event->delete();

        return redirect()->back()->with(['notifications' => [
            'message' => 'Event deleted successfully.',
            'type' => 'success',
        ],
        ]);
    }
}
