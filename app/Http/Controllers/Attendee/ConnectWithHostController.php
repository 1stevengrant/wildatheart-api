<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Spatie\SlackAlerts\Facades\SlackAlert;

class ConnectWithHostController extends Controller
{
    public function index(Event $event)
    {
        return inertia('ConnectWithHost', [
            'event' => $event->inertiaFilter(),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $request->validate([
            'family_name' => 'required|string|max:255|min:2',
            'given_name' => 'required|string|max:255|min:2',
            'email' => 'required|email|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'phone' => 'required|min:9|max:20',
            'opted_into_marketing' => 'required|boolean',
        ]);

        $attendee = $event->attendees()->create([
            'user_id' => $event->host->id,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'city' => $request->city,
            'state' => $request->state,
            'country' => getCountryCodeFromCountryName($request->country),
            'country_code' => getCountryCodeFromCountryName($request->country),
            'marketing_updates' => $request->opted_into_marketing,
        ]);

        activity()
            ->withProperties(['attendee' => $attendee, 'event' => $event])
            ->performedOn($attendee)
            ->causedBy($event)
            ->log('Connected with Host');

        if (app()->isProduction()) {
            SlackAlert::to('events')
                ->message($attendee->fullName.' contacted '
                    .$event->host->fullName.' about their event in '.$event->location.' !');
        }

        return to_route('view.event', $event)->with(['notifications' => [
            'message' => "Connected with {$event->host->fullName}!",
            'connectedWithHost' => true,
            'type' => 'success',
        ],
        ]);
    }
}
