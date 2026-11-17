<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Support\EventTypeDateSelector;
use Lorisleiva\Actions\Concerns\AsAction;
use App\DataTransferObjects\EventCreationData;

class CreateApiEvent
{
    use AsAction;

    public function handle(User $host, EventType $eventType, EventCreationData $data): Event
    {
        $event = $host->events()->create([
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
            'frequency' => EventTypeDateSelector::requiresFrequency($eventType->group_type)
                ? $data->frequency
                : null,
            'sex' => $eventType->sex,
            'number_of_items_in_material_order' => $data->materialsQuantity,
        ]);

        SendMaterialsOrderToZapier::run(
            event: $event,
            eventType: $eventType,
            user: $host,
            quantity: $data->materialsQuantity,
        );

        return $event;
    }
}
