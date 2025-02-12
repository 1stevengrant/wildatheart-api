<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Services\SeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Jobs\Event\IncreaseEventCountsJob;

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

        IncreaseEventCountsJob::dispatch(event: $event, countType: Event::COUNT_TYPE_VIEW);

        $this->seoService->setMetadata(
            title: "Connect with {$event->host_name} for {$event->name} in {$event->location}",
            description: preg_replace('/\s+/', ' ', $event->eventType->card_teaser_text),
            image: bunnyImage($event->eventType->image),
            url: url()->current(),
        );

        dispatch(new LogActivityJob(
            logName: 'events',
            description: 'Viewed event listing',
            properties: ['event' => $event, 'user' => auth()->user() ?? null],
            causedBy: auth()->user() ? auth()->user()->id : null,
            performedOn: $event,
        ));

        return inertia('ViewEventPage', [
            'event' => EventResource::make($event)->resolve(),
        ]);
    }
}
