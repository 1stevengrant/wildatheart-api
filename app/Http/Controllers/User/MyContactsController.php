<?php

namespace App\Http\Controllers\User;

use Inertia\Response;
use App\Data\AttendeeData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class MyContactsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $paginatedAttendees = auth()->user()->attendees()
            ->with(['event.eventType'])
            ->orderByName()
            ->filter(Request::only('event_id'))
            ->paginate(10)
            ->appends(Request::all());

        return inertia('Profile/MyContactsPage', [
            'filters' => Request::all('event_id'),
            'myEvents' => auth()->user()->events()->get(),
            'attendees' => $this->transformPaginatedData($paginatedAttendees, AttendeeData::class),
        ]);
    }
}
