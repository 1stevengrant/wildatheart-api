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
        $upcomingEvents = cache()->remember('public_upcoming_events', now()->addHour(), function () {
            return Event::query()
                ->with(['user', 'eventType', 'managers'])
                ->upcoming()
                ->public()
                ->published()
                ->get();
        });

        // Group the models first
        $groupedEvents = $upcomingEvents->groupBy(function ($event) {
            return Carbon::parse($event->start_date)->format('M Y');
        });

        // Sort the groups by date
        $sortedGroups = $groupedEvents->sortBy(function ($events, $monthYear) {
            // Take the first event from each group to get the sort date
            return Carbon::parse($events->first()->start_date);
        });

        // Then convert each group to DataCollection
        $upcomingEvents = $sortedGroups->map(function ($events) {
            return EventData::collect($events);
        });

        return inertia('UpcomingEventsPage', [
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
