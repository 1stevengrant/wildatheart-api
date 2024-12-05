<?php

namespace App\Http\Controllers\Event;

use App\Actions\Event\ApproveEvent;
use App\Actions\Event\CheckWelcomeStatus;
use App\Actions\Event\CreateFireEventType;
use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\FireEventRequest;
use App\Jobs\LogActivityJob;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HostEventTypeCaptivatingFireController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        return inertia('HostCaptivatingFirePage', [
            'eventType' => EventType::where('name', EventType::CAPTIVATING_FIRE)->first(),
        ]);
    }

    public function store(FireEventRequest $request)
    {
        $request->validated();

        $event = CreateFireEventType::make()->handle(
            eventType: EventType::where('name', EventType::CAPTIVATING_FIRE)->first(),
            data: $request->all()
        );

        UpdateUserRole::run(
            user: auth()->user(),
            role: User::ROLE_HOST
        );

        if (! auth()->user()->hasVerifiedEmail()) {
            SendVerificationEmail::run(event: $event);

            return Inertia::location(to_route('confirm.email', ['event' => $event]));
        }

        ApproveEvent::run(event: $event);

        CheckWelcomeStatus::run(event: $event);

        return Inertia::location(to_route('gift.index', ['event' => $event]));
    }
}
