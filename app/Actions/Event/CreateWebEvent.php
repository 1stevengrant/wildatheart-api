<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Exceptions\EventCreationException;
use App\DataTransferObjects\EventCreationData;

class CreateWebEvent
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
                'city' => $data->city,
                'zip' => $data->zip,
                'country' => $data->country,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'region' => $data->region,
                'district' => $data->district,
                'locality' => $data->locality,
                'neighborhood' => $data->neighborhood,
                'street_address' => $data->streetAddress,
                'is_private' => $data->isPrivate,
                'state' => $data->state,
                'start_date' => $data->startDate,
                'end_date' => $data->endDate,
                'sex' => $eventType->sex,
                'number_of_items_in_material_order' => $data->materialsQuantity,
            ]),
        );

        ApproveEvent::run(event: $event);

        SendMaterialsOrderToZapier::run(
            event: $event,
            eventType: $eventType,
            user: $host,
            quantity: $data->materialsQuantity,
        );

        return $event;
    }
}
