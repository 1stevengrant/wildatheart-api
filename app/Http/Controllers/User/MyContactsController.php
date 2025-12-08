<?php

namespace App\Http\Controllers\User;

use Inertia\Response;
use App\Data\AttendeeData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request;

class MyContactsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $paginatedAttendees = \App\Models\Attendee::query()
            ->controllableByUser($user)
            ->with(['event.eventType'])
            ->orderByName()
            ->filter(Request::only('event_id'))
            ->paginate(10)
            ->appends(Request::all());

        $myEvents = \App\Models\Event::query()
            ->controllableByUser($user)
            ->get();

        return inertia('Profile/MyContactsPage', [
            'filters' => Request::all('event_id'),
            'myEvents' => $myEvents,
            'attendees' => $this->transformPaginatedData($paginatedAttendees, AttendeeData::class),
        ]);
    }

    public function destroy(int $attendeeId): RedirectResponse
    {
        $attendee = \App\Models\Attendee::query()
            ->controllableByUser(auth()->user())
            ->findOrFail($attendeeId);

        $attendee->delete();

        return to_route('my.contacts')->with([
            'notifications' => [
                'message' => 'Contact removed successfully.',
                'type' => 'success',
            ],
        ]);
    }
}
