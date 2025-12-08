<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\Request;

class FiresPageController extends Controller
{
    public function __invoke(Request $request)
    {
        $fires = cache()->remember('public_fires', now()->addHour(), function () {
            return Event::query()
                ->fires()
                ->public()
                ->published()
                ->with(['user', 'eventType', 'managers'])
                ->get();
        });

        return inertia('UpcomingFiresPage', [
            'fires' => EventData::collection($fires),
        ]);
    }
}
