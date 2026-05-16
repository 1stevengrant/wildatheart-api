<?php

namespace App\Http\Controllers\User;

use App\Models\Event;
use Inertia\Response;
use App\Models\Attendee;
use App\Data\AttendeeData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request;

class MyContactsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $paginatedAttendees = Attendee::query()
            ->controllableByUser($user)
            ->with(['event.eventType'])
            ->orderByName()
            ->filter(Request::only('event_id'))
            ->paginate(10)
            ->appends(Request::all());

        $myEvents = Event::query()
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
        $attendee = Attendee::query()
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
