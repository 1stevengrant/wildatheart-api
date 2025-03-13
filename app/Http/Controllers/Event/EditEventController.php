<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;

class EditEventController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        if ($event->user->id !== auth()->id()) {
            return redirect()->route('home');
        }

        return inertia('Profile/EditEventPage', [
            'event' => EventResource::make($event)->resolve(),
        ]);
    }
}
