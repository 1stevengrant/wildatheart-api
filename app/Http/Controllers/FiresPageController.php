<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\Request;

class FiresPageController extends Controller
{
    public function __invoke(Request $request)
    {
        $fires = cache()->remember('public_fires.v2', now()->addHour(), function (): array {
            $events = Event::query()
                ->fires()
                ->public()
                ->published()
                ->with(['user', 'eventType', 'managers'])
                ->get();

            return EventData::collect($events)->toArray();
        });

        return inertia('UpcomingFiresPage', [
            'fires' => $fires,
        ]);
    }
}
