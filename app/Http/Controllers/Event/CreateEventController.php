<?php

namespace App\Http\Controllers\Event;

use App\Actions\Event\ApproveEvent;
use App\Actions\Event\CheckWelcomeStatus;
use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreateEventController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventRequest $request)
    {
        $request->validated();

        $event = auth()->user()->events()->create([
            'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
            'name' => request('name'),
            'street_address' => request('street_address'),
            'state' => request('state'),
            'city' => request('city'),
            'zip' => request('zip'),
            'country' => getCountryCodeFromCountryName(request('country')),
            'latitude' => request('latitude'),
            'longitude' => request('longitude'),
            'region' => request('region'),
            'district' => request('district'),
            'locality' => request('locality'),
            'neighborhood' => request('neighborhood'),
        ]);

        activity()
            ->performedOn($event)
            ->causedBy(auth()->user())
            ->log('Create Event');

        UpdateUserRole::make()->handle(
            user: auth()->user(),
            role: User::ROLE_HOST
        );

        if (! auth()->user()->hasVerifiedEmail()) {
            SendVerificationEmail::make()->handle(event: $event);

            return Inertia::location(to_route('confirm.email', ['event' => $event]));
        }

        ApproveEvent::run(event: $event);

        CheckWelcomeStatus::run(event: $event);

        return Inertia::location(to_route('gift', ['event' => $event]));
    }
}
