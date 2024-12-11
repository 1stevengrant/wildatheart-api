<?php

namespace App\Http\Controllers\Event;

use App\Models\EventType;
use App\Services\SeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostEventTypeSelectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, SeoService $seoService)
    {
        $seoService->setMetadata(
            title: 'Choose an event type to create',
            description: 'Choose an event type to create',
            image: null,
            url: url()->current()
        );

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
