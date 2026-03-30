<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Data\V1\AttendeeData;
use App\Data\ConnectWithHostData;
use App\Http\Controllers\Controller;
use App\Actions\Attendee\CreateAttendee;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

class AttendeeController extends Controller
{
    public function store(Event $event, ConnectWithHostData $data)
    {
        $attendee = CreateAttendee::run($event, [
            'given_name' => $data->given_name,
            'family_name' => $data->family_name,
            'email' => $data->email,
            'phone' => $data->phone,
            'city' => $data->city,
            'state' => $data->state,
            'country' => $data->country,
            'marketing_updates' => $data->opted_into_marketing,
        ]);

        LogActivityJob::dispatch(
            description: 'Connected with Host',
            properties: ['attendee' => $attendee, 'event' => $event],
            causedBy: $event,
            performedOn: $attendee,
        );

        if ($attendee->hasOptedIntoMarketing()) {
            SubscribeToCommunicationListJob::dispatch(
                listMember: $attendee,
                listId: config('services.wegive.marketing_list_id')
            );
        }

        IncreaseEventCountsJob::dispatch($event, Event::COUNT_TYPE_ATTENDEE);

        PostAttendeeContactToSlackJob::dispatch($attendee, $event);

        return AttendeeData::fromAttendee($attendee);
    }
}
