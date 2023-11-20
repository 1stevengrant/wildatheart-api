<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;

class UpdateEventController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventRequest $request, Event $event)
    {
        abort_unless(\Gate::allows('update', $event), 403);

        $request->validated();

        $event->update($request->all());

        activity()
            ->performedOn($event)
            ->causedBy(auth()->user())
            ->log('Update Event');

        return to_route('my.events')->with(['notifications' => [
            'message' => 'Event updated successfully!',
            'type' => 'success',
        ],
        ]);
    }
}
