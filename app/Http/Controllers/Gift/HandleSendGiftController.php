<?php

namespace App\Http\Controllers\Gift;

use App\Models\Event;
use App\Jobs\PostToZapier;
use App\Mail\Event\GiftRequested;
use App\Actions\Event\UpdateEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Actions\Address\CreateAddress;
use App\Http\Requests\HandleSendGiftRequest;
use App\DataTransferObjects\GoogleSheetDataTransferObject;

class HandleSendGiftController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Event $event, HandleSendGiftRequest $request)
    {
        $request->validated();

        CreateAddress::run([
            'user_id' => auth()->user()->id,
            'given_name' => request('given_name'),
            'family_name' => request('family_name'),
            'company' => request('company'),
            'street_address' => request('street_address'),
            'address2' => request('address2'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'phone' => request('phone'),
            'is_primary' => true,
            'country' => request('country'),
            'event_id' => $event->id,
        ]);

        UpdateEvent::run(
            data: ['gift_requested' => true],
            event: $event
        );

        Mail::to($event->user->email)
            ->send(new GiftRequested($event));

        $dataTransferObject = new GoogleSheetDataTransferObject(
            webhookUrl: $event->eventType->zapier_webhook_url,
            eventId: $event->id,
            eventType: $event->eventType->name,
            email: $event->user->email,
            quantity: '1',
            first_name: request('given_name'),
            last_name: request('family_name'),
            address_1: request('street_address'),
            address_2: request('address2'),
            city: request('city'),
            state: request('state'),
            zip: request('zip'),
            country: request('country'),
            date: now()->toDateString()
        );

        PostToZapier::dispatch($dataTransferObject);

        return to_route('my.events.active')->with([
            'notifications' => [
                'message' => 'Your free gift has been requested!',
                'type' => 'success',
            ],
        ]);
    }
}
