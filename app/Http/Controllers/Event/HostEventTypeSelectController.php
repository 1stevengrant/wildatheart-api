<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostEventTypeSelectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return inertia('Event/HostEventTypeSelectPage', [
            'experienceTypes' => EventType::query()->slug('%experience%')->active()->get(),
            'retreatTypes' => EventType::query()->slug('%retreat%')
                ->slugOr('%school%')
                ->active()
                ->get(),
            'fireTypes' => EventType::query()->slug('%fire%')->active()->get(),
        ]);
    }
}
