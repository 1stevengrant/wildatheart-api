<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Jobs\PostToZapier;
use App\Data\MailingAddressData;
use App\Mail\Event\GiftRequested;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Actions\Address\SetPrimaryMailingAddress;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

class RequestEventGift
{
    use AsAction;

    public function __construct(private readonly SetPrimaryMailingAddress $setPrimaryMailingAddress) {}

    public function handle(User $user, Event $event, MailingAddressData $address): void
    {
        DB::transaction(function () use ($user, $event, $address): void {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);

            abort_if($lockedEvent->hasRequestedGift(), 403, 'You have already requested your gift.');

            $this->setPrimaryMailingAddress->handle($user, $address, $lockedEvent);

            $lockedEvent->update(['gift_requested' => true]);

            DB::afterCommit(fn () => $this->dispatchNotifications($lockedEvent, $address));
        });
    }

    private function dispatchNotifications(Event $event, MailingAddressData $address): void
    {
        Mail::to($event->user->email)->send(new GiftRequested($event));

        PostToZapier::dispatch(new GoogleSheetDataTransferObject(
            webhookUrl: $event->eventType->zapier_webhook_url,
            eventId: $event->id,
            eventType: $event->eventType->name,
            email: $event->user->email,
            quantity: '1',
            first_name: $address->given_name,
            last_name: $address->family_name,
            address_1: $address->street_address,
            address_2: $address->address2,
            city: $address->city,
            state: $address->state,
            zip: $address->zip,
            country: $address->country,
            date: now()->toDateString(),
        ));
    }
}
