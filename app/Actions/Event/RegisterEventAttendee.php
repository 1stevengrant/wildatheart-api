<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Models\Attendee;
use App\Jobs\LogActivityJob;
use App\Data\ConnectWithHostData;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Jobs\Event\IncreaseEventCountsJob;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;
use App\Jobs\WeGive\SubscribeToCommunicationListJob;

class RegisterEventAttendee
{
    use AsAction;

    public function handle(Event $event, ConnectWithHostData $data): Attendee
    {
        $countryCode = getCountryCodeFromCountryName($data->country);

        $attendee = $event->attendees()->create([
            'user_id' => $event->user->id,
            'given_name' => $data->given_name,
            'family_name' => $data->family_name,
            'email' => $data->email,
            'phone' => $data->phone,
            'city' => $data->city,
            'state' => $data->state,
            'country' => $countryCode,
            'country_code' => $countryCode,
            'marketing_updates' => $data->opted_into_marketing,
        ]);

        LogActivityJob::dispatch(
            description: 'Connected with Host',
            causedBy: $event,
            performedOn: $attendee,
        );

        if ($attendee->hasOptedIntoMarketing()) {
            SubscribeToCommunicationListJob::dispatch(
                listId: config('services.wegive.marketing_list_id'),
                listMember: $attendee,
            );
        }

        IncreaseEventCountsJob::dispatch($event, Event::COUNT_TYPE_ATTENDEE);

        PostAttendeeContactToSlackJob::dispatch($attendee, $event);

        return $attendee;
    }
}
