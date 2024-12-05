<?php

namespace App\Http\Controllers\Event;

use App\Actions\Event\ApproveEvent;
use App\Actions\Event\CheckWelcomeStatus;
use App\Actions\Event\CreateFireEventType;
use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\FireEventRequest;
use App\Models\EventType;
use App\Models\User;
use Inertia\Inertia;

abstract class AbstractFireEventController extends Controller
{
    abstract protected function getEventType(): string;

    abstract protected function getViewName(): string;

    public function index()
    {
        return inertia($this->getViewName(), [
            'eventType' => EventType::where('name', $this->getEventType())->first(),
        ]);
    }

    public function store(FireEventRequest $request)
    {
        $request->validated();

        $event = CreateFireEventType::make()->handle(
            eventType: EventType::where('name', $this->getEventType())->first(),
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
