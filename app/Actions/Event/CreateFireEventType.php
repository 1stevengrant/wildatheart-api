<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Enums\UserRoleEnum;
use App\Jobs\LogActivityJob;
use App\Actions\User\UpdateUserRole;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Exceptions\EventCreationException;
use App\DataTransferObjects\EventCreationData;

class CreateFireEventType
{
    use AsAction;

    /** @throws EventCreationException */
    public function handle(User $host, EventType $eventType, EventCreationData $data): Event
    {
        $event = PreventDuplicateEventCreation::run(
            $host,
            fn (): Event => $host->events()->create([
                'event_type_id' => $eventType->id,
                'name' => $data->name,
                'street_address' => $data->streetAddress,
                'state' => $data->state,
                'city' => $data->city,
                'zip' => $data->zip,
                'country' => getCountryCodeFromCountryName($data->country ?? ''),
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'region' => $data->region,
                'district' => $data->district,
                'locality' => $data->locality,
                'neighborhood' => $data->neighborhood,
                'sex' => $eventType->sex,
                'start_date' => $data->startDate,
                'frequency' => $data->frequency,
            ]),
        );

        LogActivityJob::dispatch(
            description: 'Create Event',
            causedBy: $host,
            performedOn: $event,
        );

        UpdateUserRole::run(
            user: $host,
            role: UserRoleEnum::Host,
        );

        ApproveEvent::run(event: $event);

        return $event;
    }
}
