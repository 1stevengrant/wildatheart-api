<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeCollection;
use Illuminate\Support\Facades\Request;
use Inertia\Response;

class MyContactsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {

        return inertia('Profile/MyContactsPage', [
            'filters' => Request::all('event_id'),
            'myEvents' => auth()->user()->events()->get(),
            'attendees' => new AttendeeCollection(
                auth()->user()->attendees()
                    ->orderByName()
                    ->filter(Request::only('event_id'))
                    ->paginate()
                    ->appends(Request::all())
            ),
        ]);
    }
}
