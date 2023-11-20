<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class ViewEventListingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        abort_unless(
            $event->user_id === optional(auth()->user())->id || $event->approved_at,
            404
        );

        return inertia('ViewEvent', [
            'event' => $event->inertiaFilter(),
        ]);
    }
}
