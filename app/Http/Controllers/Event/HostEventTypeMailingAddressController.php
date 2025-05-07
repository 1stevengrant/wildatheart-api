<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MailingAddressRequest;
use App\Services\EventCreationStepHandlerService;

class HostEventTypeMailingAddressController extends Controller
{
    public function __construct(
        protected EventCreationStepHandlerService $eventCreationStepHandlerService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EventType $eventType)
    {
        $this->eventCreationStepHandlerService->markStepComplete('guide', 'faqs');

        return inertia('Event/MailingAddressEventTypePage', [
            'eventType' => $eventType,
            'address' => auth()->user()->addresses()
                ->where('is_primary', true)
                ->first(),
            'eventSteps' => session('event_steps'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventType $eventType, MailingAddressRequest $request)
    {
        auth()->user()->addresses()->update([
            'is_primary' => false,
        ]);

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

        return to_route('host.eventType.create', $eventType);
    }
}
