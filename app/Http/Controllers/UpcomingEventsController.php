<?php

namespace App\Http\Controllers;

use App\Http\Resources\API\EventResource;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UpcomingEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $upcomingEvents = cache()->remember('public_upcoming_events', now()->addHour(), function () {
            return Event::query()
                ->upcoming()
                ->public()
                ->published()
                ->get();
        });
        $upcomingEvents = EventResource::collection($upcomingEvents);

        $upcomingEvents = $upcomingEvents->groupBy(function ($val) {
            return Carbon::parse($val->start_date)->format('M Y');
        });

        return inertia('UpcomingEventsPage', [
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
