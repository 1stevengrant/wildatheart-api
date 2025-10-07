<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\V1\EventData;
use App\Data\V1\EventCollectionData;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function index()
    {
        return cache()->remember('events_collection', now()->addHour(), function () {
            return EventCollectionData::collection(
                Event::query()
                    ->approved()
                    ->active()
                    ->notPrivate()
                    ->with(['eventType', 'user'])
                    ->orderBy('start_date')
                    ->get()
            );
        });
    }

    public function show(Event $event)
    {
        return EventData::from($event);
    }
}
