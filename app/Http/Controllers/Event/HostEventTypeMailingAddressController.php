<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\EventType;
use App\Data\MailingAddressData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\MailingAddressRequest;
use Illuminate\Container\Attributes\CurrentUser;
use App\Actions\Address\SetPrimaryMailingAddress;
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
        $this->eventCreationStepHandlerService->markStepComplete('guide.faqs');

        return inertia('Event/MailingAddressEventTypePage', [
            'eventType' => $eventType,
            'address' => auth()->user()->addresses()
                ->where('is_primary', true)
                ->first(),
            'eventSteps' => $this->eventCreationStepHandlerService->eventSteps(),
        ]);
    }

    public function store(EventType $eventType, MailingAddressRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        SetPrimaryMailingAddress::run($user, MailingAddressData::from($request->validated()));

        return to_route('host.eventType.create', $eventType);
    }
}
