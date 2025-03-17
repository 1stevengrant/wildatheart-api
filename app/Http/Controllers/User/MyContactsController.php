<?php

namespace App\Http\Controllers\User;

use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\AttendeeCollection;

class MyContactsController extends Controller
{
    public function __invoke(Request $request): Response
    {

        return inertia('Profile/MyContactsPage', [
            'filters' => Request::all('event_id'),
            'myEvents' => auth()->user()->events()->get(),
            'attendees' => new AttendeeCollection(
                auth()->user()->attendees()
                    ->with(['event.eventType'])
                    ->orderByName()
                    ->filter(Request::only('event_id'))
                    ->paginate()
                    ->appends(Request::all())
            ),
        ]);
    }
}
