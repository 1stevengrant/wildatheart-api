<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\V1\EventData;
use Statamic\Facades\Entry;
use App\Data\V1\EventCollectionData;
use App\Http\Controllers\Controller;
use App\Services\EventResponseBuilder;

class EventController extends Controller
{
    public function __construct(private readonly EventResponseBuilder $eventResponseBuilder) {}

    public function index()
    {
        return cache()->remember('events_collection', now()->addHour(), function () {
            return EventCollectionData::collect(
                Event::query()
                    ->published()
                    ->active()
                    ->public()
                    ->hasEventType()
                    ->with(['eventType', 'user'])
                    ->orderBy('start_date')
                    ->get()
            );
        });
    }

    public function show(Event $event)
    {
        abort_if($event->eventType === null, 404, 'Event is missing an event type and cannot be served.');

        $eventData = EventData::from($event);
        $landingPage = Entry::find($event->eventType->related_landing_page_id);

        if ($landingPage) {
            $eventData->landingPage = $this->eventResponseBuilder->build($landingPage);
        }

        return $eventData;
    }
}
