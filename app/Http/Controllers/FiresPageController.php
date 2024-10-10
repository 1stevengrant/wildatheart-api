<?php

namespace App\Http\Controllers;

use App\Http\Resources\API\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class FiresPageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $fires = cache()->remember('public_fires', now()->addHour(), function () {
            return Event::query()
                ->fires()
                ->public()
                ->published()
                ->get();
        });

        return inertia('UpcomingFiresPage', [
            'fires' => EventResource::collection($fires),
        ]);
    }
}
