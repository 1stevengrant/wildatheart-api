<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use Lorisleiva\Actions\Concerns\AsAction;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

class SendMaterialsOrderToZapier
{
    use AsAction;

    public function handle(
        Event $event,
        EventType $eventType,
        User $user,
        ?string $quantity,
    ): void {
        if (! $eventType->hasZapier()) {
            return;
        }

        if (! $eventType->hasMaterialsOrder()) {
            return;
        }

        if ($quantity === '0' || $quantity === null) {
            return;
        }

        $mailingAddress = $user->primaryAddress;

        if (! $mailingAddress) {
            return;
        }

        $dataTransferObject = new GoogleSheetDataTransferObject(
            webhookUrl: $eventType->zapier_webhook_url,
            eventId: $event->id,
            eventType: $eventType->name,
            email: $user->email,
            quantity: $quantity,
            first_name: $user->given_name,
            last_name: $user->family_name,
            address_1: $mailingAddress->street_address,
            address_2: $mailingAddress->address2,
            city: $mailingAddress->city,
            state: $mailingAddress->state,
            zip: $mailingAddress->zip,
            country: $mailingAddress->country,
            date: now()->toDateTimeString(),
        );

        PostToZapier::dispatch($dataTransferObject);
    }
}
