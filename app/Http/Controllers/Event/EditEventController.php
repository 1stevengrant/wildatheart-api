<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EditEventController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        if ($event->user->id !== auth()->id()) {
            return to_route('home');
        }

        return inertia('Profile/EditEventPage', [
            'event' => EventData::fromModel($event),
        ]);
    }
}
