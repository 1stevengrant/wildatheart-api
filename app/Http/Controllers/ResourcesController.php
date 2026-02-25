<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function __invoke(Request $request)
    {
        $eventTypes = cache()->remember('event_types', now()->addDay(), function () {
            return EventType::query()->active()->orderBy('order')->get();
        });

        return inertia('ResourcesPage', [
            'eventTypes' => $eventTypes->map(fn (EventType $eventType) => [
                'id' => $eventType->id,
                'slug' => $eventType->slug,
                'name' => $eventType->name,
                'image' => $eventType->image,
                'event_guide_pages' => $eventType->event_guide_pages,
                'video_link' => $eventType->video_link,
                'action_buttons' => $eventType->action_buttons,
            ])->values(),
        ]);
    }
}
