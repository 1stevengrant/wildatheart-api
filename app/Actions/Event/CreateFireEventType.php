<?php

namespace App\Actions\Event;

use App\DataTransferObjects\CreateEventDataTransferObject;
use App\Jobs\LogActivityJob;
use App\Models\EventType;
use Lorisleiva\Actions\Concerns\AsAction;

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
        ]);

        dispatch(new LogActivityJob(
            description: 'Create Event',
            causedBy: $user,
            performedOn: $event,
        ));

        return $event;
    }
}
