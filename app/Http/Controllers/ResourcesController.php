<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Data\EventTypeData;
use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function __invoke(Request $request)
    {
        $eventTypes = cache()->remember('event_types', now()->addDay(), function () {
            return EventType::query()->active()->orderBy('order')->get();
        });

        return inertia('ResourcesPage', [
            'eventTypes' => EventTypeData::collection($eventTypes),
        ]);
    }
}
