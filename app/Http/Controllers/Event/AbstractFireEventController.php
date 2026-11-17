<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use Inertia\Inertia;
use App\Models\EventType;
use App\Data\EventTypeData;
use App\Data\FireEventData;
use App\Http\Controllers\Controller;
use App\Actions\Event\CreateFireEventType;
use App\Exceptions\EventCreationException;
use App\DataTransferObjects\EventCreationData;
use Illuminate\Container\Attributes\CurrentUser;

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

    public function store(FireEventData $data, #[CurrentUser] User $user)
    {
        try {
            $event = CreateFireEventType::run(
                host: $user,
                eventType: EventType::where('name', $this->getEventType())->first(),
                data: EventCreationData::fromFireData($data),
            );
        } catch (EventCreationException $exception) {
            return back()->withErrors(['duplicate' => $exception->getMessage()]);
        }

        return Inertia::location(to_route('gift.index', ['event' => $event]));
    }
}
