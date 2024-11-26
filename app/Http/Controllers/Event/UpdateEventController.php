<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\FireEventRequest;
use App\Models\Event;
use App\Support\EventDateHelper;

class UpdateEventController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(FireEventRequest $request, Event $event)
    {
        abort_unless(\Gate::allows('update', $event), 403);

        $request->validated();

        EventDateHelper::adjustEndDateForRetreat($request, $event->eventType->name);

        $event->update($request->all());

        return to_route('my.events.active')->with(['notifications' => [
            'message' => 'Event updated successfully!',
            'type' => 'success',
        ],
        ]);
    }
}
