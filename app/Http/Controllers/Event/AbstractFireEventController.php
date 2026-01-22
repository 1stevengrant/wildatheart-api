<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use Inertia\Inertia;
use App\Models\EventType;
use App\Data\EventTypeData;
use App\Actions\Event\ApproveEvent;
use App\Actions\User\UpdateUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\FireEventRequest;
use App\Actions\Event\CreateFireEventType;

abstract class AbstractFireEventController extends Controller
{
    abstract protected function getEventType(): string;

    abstract protected function getViewName(): string;

    public function index()
    {
        return inertia($this->getViewName(), [
            'eventType' => EventTypeData::fromModel(EventType::where('name', $this->getEventType())->first()),
        ]);
    }

    public function store(FireEventRequest $request)
    {
        // Prevent duplicate event creation within a short time window
        if (auth()->user()->events()->where('created_at', '>', now()->subSeconds(10))->exists()) {
            return back()->withErrors([
                'duplicate' => 'An event was just created. Please wait a moment before creating another.',
            ]);
        }

        $event = CreateFireEventType::make()->handle(
            eventType: EventType::where('name', $this->getEventType())->first(),
            data: $request->all()
        );

        UpdateUserRole::run(
            user: auth()->user(),
            role: User::ROLE_HOST
        );

        ApproveEvent::run(event: $event);

        return Inertia::location(to_route('gift.index', ['event' => $event]));
    }
}
