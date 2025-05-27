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
                ->upcoming()
                ->public()
                ->published()
                ->get();
        });
        $upcomingEvents = EventData::collection($upcomingEvents);

        $upcomingEvents = $upcomingEvents->groupBy(function ($val) {
            return Carbon::parse($val->start_date)->format('M Y');
        });

        return inertia('UpcomingEventsPage', [
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
