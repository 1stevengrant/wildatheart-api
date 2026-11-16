<?php

namespace App\Http\Controllers\Gift;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Data\MailingAddressData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Actions\Event\RequestEventGift;
use App\Http\Requests\HandleSendGiftRequest;
use Illuminate\Container\Attributes\CurrentUser;

class SendGiftController extends Controller
{
    public function index(Request $request, Event $event)
    {
        abort_unless(! $event->hasRequestedGift(), 403, 'You have already requested your gift.');

        return inertia('SendGiftPage', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'longitude' => $event->longitude,
                'latitude' => $event->latitude,
                'display_longitude' => $event->display_longitude ?? $event->longitude,
                'display_latitude' => $event->display_latitude ?? $event->latitude,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'event_type' => $event->eventType,
                'type_group' => $event->eventType->group_type,
                'location' => $event->location,
                'country' => $event->country,
                'host_name' => $event->user->fullname,
            ],
        ]);
    }

    public function store(Event $event, HandleSendGiftRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        RequestEventGift::run($user, $event, MailingAddressData::from($request->validated()));

        return to_route('my.events.active')->with([
            'notifications' => [
                'message' => 'Your free gift has been requested!',
                'type' => 'success',
            ],
        ]);
    }
}
