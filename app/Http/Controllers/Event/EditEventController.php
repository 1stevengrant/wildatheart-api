<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EditEventController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        if ($event->host->id !== auth()->id()) {
            return redirect()->route('home');
        }

        return inertia('Profile/EditEvent', [
            'event' => $event->inertiaFilter(),
        ]);
    }
}
