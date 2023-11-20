<?php

namespace App\Http\Controllers\Gift;

use App\Actions\Address\CreateAddress;
use App\Actions\Event\UpdateEvent;
use App\Http\Controllers\Controller;
use App\Jobs\PostToZapier;
use App\Mail\Event\GiftRequested;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class HandleSendGiftController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        $request->validate([
            'country' => 'required|max:255',
            'given_name' => 'required|max:255|min:2',
            'family_name' => 'required|max:255|min:2',
            'company' => 'nullable',
            'street_address' => 'required|max:255',
            'address2' => 'nullable',
            'city' => 'required|max:255',
            'state' => ['required'],
            'zip' => 'required|max:20|min:4',
            'phone' => 'required',
        ]);

        CreateAddress::run([
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

        Mail::to($event->host->email)
            ->send(new GiftRequested($event));

        $data = request()->only([
            'given_name', 'family_name', 'street_address', 'address2', 'city', 'state', 'zip', 'country',
        ]);

        $data['email'] = $event->host->email;

        PostToZapier::dispatch(data: $data);

        return to_route('gift.success', $event);
    }
}
