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

    public function destroy(int $attendeeId): RedirectResponse
    {
        $attendee = auth()->user()->attendees()->findOrFail($attendeeId);

        if ($attendee->user_id !== auth()->id()) {
            return to_route('my.contacts')->with([
                'notifications' => [
                    'message' => 'You do not have permission to remove this contact.',
                    'type' => 'error',
                ],
            ]);
        }

        $attendee->delete();

        return to_route('my.contacts')->with([
            'notifications' => [
                'message' => 'Contact removed successfully.',
                'type' => 'success',
            ],
        ]);
    }
}
