<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\V1\EventData;
use Statamic\Facades\Entry;
use App\Services\EventCacheService;
use App\Data\V1\EventCollectionData;
use App\Http\Controllers\Controller;
use App\Services\EventResponseBuilder;
use App\Support\Schedule\PublicScheduleDates;

class EventController extends Controller
{
    public function __construct(
        private readonly EventResponseBuilder $eventResponseBuilder,
        private readonly EventCacheService $eventCacheService,
    ) {}

    public function index()
    {
        return cache()->remember($this->eventCacheService->collectionCacheKey(), now()->addHour(), function (): array {
            return EventCollectionData::collect(
                Event::query()
                    ->published()
                    ->active()
                    ->public()
                    ->hasEventType()
                    ->with(['eventType', 'user', ...PublicScheduleDates::RELATIONS])
                    ->orderBy('start_date')
                    ->get()
            )->toArray();
        });
    }

    public function show(Event $event)
    {
        abort_if($event->eventType === null, 404, 'Event is missing an event type and cannot be served.');

        $eventData = EventData::from($event->load(PublicScheduleDates::RELATIONS));
        $landingPage = Entry::find($event->eventType->related_landing_page_id);

        if ($landingPage) {
            $eventData->landingPage = $this->eventResponseBuilder->build($landingPage);
        }

        return $eventData;
    }
}
