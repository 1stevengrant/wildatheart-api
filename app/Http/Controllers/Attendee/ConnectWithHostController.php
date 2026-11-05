<?php

namespace App\Http\Controllers\Attendee;

use App\Models\Event;
use App\Data\EventData;
use App\Jobs\LogActivityJob;
use App\Services\SeoService;
use App\Http\Controllers\Controller;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Http\Requests\ConnectWithHostRequest;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

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

        LogActivityJob::dispatch(
            description: 'Connected with Host',
            causedBy: $event,
            performedOn: $attendee,
        );

        if ($attendee->hasOptedIntoMarketing()) {
            SubscribeToCommunicationListJob::dispatch(
                listId: config('services.wegive.marketing_list_id'),
                listMember: $attendee
            );
        }

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
