<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\Request;

class UpcomingEventsController extends Controller
{
    public function __invoke(Request $request)
    {
        $upcomingEvents = cache()->remember('public_upcoming_events.v2', now()->addHour(), function (): array {
            $events = Event::query()
                ->with(['user', 'eventType', 'managers'])
                ->upcoming()
                ->public()
                ->published()
                ->get();

            return $events
                ->groupBy(fn (Event $event): string => Carbon::parse($event->start_date)->format('M Y'))
                ->sortBy(fn ($events) => Carbon::parse($events->first()->start_date))
                ->map(fn ($events): array => EventData::collect($events)->toArray())
                ->toArray();
        });

        return inertia('UpcomingEventsPage', [
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
