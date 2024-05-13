<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailingAddressRequest;
use App\Models\EventType;

class HostEventTypeMailingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EventType $eventType)
    {
        return inertia('Event/MailingAddressEventTypePage', [
            'eventType' => $eventType,
            'address' => auth()->user()->addresses()->first(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventType $eventType, MailingAddressRequest $request)
    {
        auth()->user()->addresses()->create([
            'user_id' => auth()->id(),
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'street_address' => $request->street_address,
            'address2' => $request->address2,
            'company' => $request->company,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'is_primary' => true,
        ]);

        return redirect()->route('host.eventType.create', $eventType);
    }
}
