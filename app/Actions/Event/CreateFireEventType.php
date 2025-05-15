<?php

namespace App\Actions\Event;

use App\Models\EventType;
use App\Jobs\LogActivityJob;
use Lorisleiva\Actions\Concerns\AsAction;
use App\DataTransferObjects\CreateEventDataTransferObject;

class CreateFireEventType
{
    use AsAction;

    public function handle(EventType $eventType, array $data)
    {
        $eventDataTransferObject = CreateEventDataTransferObject::fromArray($data);
        $user = auth()->user();

        $event = $user->events()->create([
            'event_type_id' => $eventType->id,
            'name' => $eventDataTransferObject->name,
            'street_address' => $eventDataTransferObject->streetAddress,
            'state' => $eventDataTransferObject->state,
            'city' => $eventDataTransferObject->city,
            'zip' => $eventDataTransferObject->zip,
            'country' => getCountryCodeFromCountryName($eventDataTransferObject->country),
            'latitude' => $eventDataTransferObject->latitude,
            'longitude' => $eventDataTransferObject->longitude,
            'region' => $eventDataTransferObject->region,
            'district' => $eventDataTransferObject->district,
            'locality' => $eventDataTransferObject->locality,
            'neighborhood' => $eventDataTransferObject->neighborhood,
            'sex' => $eventType->sex,
            'start_date' => $eventDataTransferObject->startDate,
            'frequency' => $eventDataTransferObject->frequency,
        ]);

        LogActivityJob::dispatch(
            description: 'Create Event',
            causedBy: $user,
            performedOn: $event,
        );

        return $event;
    }
}
