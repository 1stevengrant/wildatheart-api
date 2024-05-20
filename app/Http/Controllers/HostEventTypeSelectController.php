<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;

class HostEventTypeSelectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = EventType::query();

        return inertia('Event/HostEventTypeSelectPage', [
            'experienceTypes' => $query->slug('%experience%')->active()->get(),
            'retreatTypes' => $query->slug('%retreat%')
                ->slugOr('%school%')
                ->active()
                ->get(),
            'fireTypes' => $query->slug('%fire%')->active()->get(),
        ]);
    }
}
