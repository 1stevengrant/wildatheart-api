<?php

namespace App\Http\Controllers\Attendee;

use App\Models\Event;
use App\Data\EventData;
use App\Services\SeoService;
use App\Data\ConnectWithHostData;
use App\Http\Controllers\Controller;
use App\Actions\Event\RegisterEventAttendee;
use App\Http\Requests\ConnectWithHostRequest;

class ConnectWithHostController extends Controller
{
    public function __construct(public SeoService $seoService) {}

    public function index(Event $event)
    {
        $this->seoService->setEventMetadata($event);

        return inertia('ConnectWithHostPage', [
            'event' => EventData::fromModel($event),
        ]);
    }

    public function store(ConnectWithHostRequest $request, Event $event)
    {
        RegisterEventAttendee::run($event, ConnectWithHostData::from($request->validated()));

        return to_route('view.event', $event)->with(['notifications' => [
            'message' => "Connected with {$event->user->fullname}!",
            'connectedWithHost' => true,
            'type' => 'success',
        ],
        ]);
    }
}
