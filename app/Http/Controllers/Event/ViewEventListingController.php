<?php

namespace App\Http\Controllers\Event;

use App\Actions\IncreaseEventCounts;
use App\Models\Event;
use App\Services\SeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;

class ViewEventListingController extends Controller
{
    public function __construct(public SeoService $seoService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        if (! $event->isApproved() && $event->user_id !== auth()->id()) {
            return redirect()->route('login');
        }

        IncreaseEventCounts::dispatch($event, Event::COUNT_TYPE_VIEW);

        $this->seoService->setMetadata(
            title: "Connect with {$event->host_name} for {$event->name} in {$event->location}",
            description: preg_replace('/\s+/', ' ', $event->eventType->card_teaser_text),
            image: bunnyImage($event->eventType->image),
            url: url()->current(),
        );

        return inertia('ViewEventPage', [
            'event' => EventResource::make($event)->resolve(),
        ]);
    }
}
