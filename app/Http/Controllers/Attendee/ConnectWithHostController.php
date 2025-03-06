<?php

namespace App\Http\Controllers\Attendee;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Services\SeoService;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Jobs\PostAttendeeContactToSlackJob;
use App\Http\Requests\ConnectWithHostRequest;

class ConnectWithHostController extends Controller
{
    public function __construct(public SeoService $seoService) {}

    public function index(Event $event)
    {
        $this->seoService->setMetadata(
            title: "Connect with {$event->host_name} for {$event->name} in {$event->location}",
            description: $event->eventType->card_teaser_text ?? '',
            image: bunnyImage($event->eventType->image),
            url: url()->current()
        );

        return inertia('ConnectWithHostPage', [
            'event' => EventResource::make($event)->resolve(),
        ]);
    }

    public function store(ConnectWithHostRequest $request, Event $event)
    {
        $request->validated();

        $attendee = $event->attendees()->create([
            'user_id' => $event->user->id,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'city' => $request->city,
            'state' => $request->state,
            'country' => getCountryCodeFromCountryName($request->country),
            'country_code' => getCountryCodeFromCountryName($request->country),
            'marketing_updates' => $request->opted_into_marketing,
        ]);

        dispatch(new LogActivityJob(
            description: 'Connected with Host',
            properties: ['attendee' => $attendee, 'event' => $event],
            causedBy: $event,
            performedOn: $attendee,
        ));

        IncreaseEventCountsJob::class::dispatch($event, Event::COUNT_TYPE_ATTENDEE);

        PostAttendeeContactToSlackJob::dispatch($attendee, $event);

        return to_route('view.event', $event)->with(['notifications' => [
            'message' => "Connected with {$event->user->fullname}!",
            'connectedWithHost' => true,
            'type' => 'success',
        ],
        ]);
    }
}
