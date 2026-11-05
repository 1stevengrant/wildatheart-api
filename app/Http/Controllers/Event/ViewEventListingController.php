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
        if (! $event->isApproved() && (! auth()->check() || ! $event->isOwnedOrManagedBy(auth()->user()))) {
            return to_route('login');
        }

        // Eager load managers relationship
        $event->load('managers');

        IncreaseEventCountsJob::dispatch(event: $event, countType: Event::COUNT_TYPE_VIEW);

        $this->seoService->setEventMetadata($event);

        LogActivityJob::dispatch(
            logName: 'events',
            description: 'Viewed event listing',
            causedBy: auth()->user() ?? null,
            performedOn: $event,
        );

        return inertia('ViewEventPage', [
            'event' => EventData::fromModel($event),
        ]);
    }
}
