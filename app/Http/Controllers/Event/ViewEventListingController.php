<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use App\Data\EventData;
use App\Jobs\LogActivityJob;
use App\Services\SeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Event\IncreaseEventCountsJob;

class ViewEventListingController extends Controller
{
    public function __construct(public SeoService $seoService) {}

    public function __invoke(Request $request, Event $event)
    {
        if (! $event->isApproved() && $event->user_id !== auth()->id()) {
            return to_route('login');
        }

        IncreaseEventCountsJob::dispatch(event: $event, countType: Event::COUNT_TYPE_VIEW);

        $this->seoService->setMetadata(
            title: "Connect with $event->host_name for $event->name in $event->location",
            description: preg_replace('/\s+/', ' ', $event->eventType->card_teaser_text),
            image: bunnyImage($event->eventType->image),
            url: url()->current(),
        );

        LogActivityJob::dispatch(
            logName: 'events',
            description: 'Viewed event listing',
            properties: ['event' => $event, 'user' => auth()->user() ?? null],
            causedBy: auth()->user() ?? null,
            performedOn: $event,
        );

        return inertia('ViewEventPage', [
            'event' => EventData::fromModel($event),
        ]);
    }
}
