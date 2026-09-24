<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\EventType;
use App\Jobs\LogActivityJob;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\Event\SendNewEventSlackAlert;
use App\Mail\Event\NewEventCreatedMailable;
use App\Http\Integrations\WeGive\WeGiveConnector;

class EventObserver
{
    public function saving(Event $event): void
    {
        if (empty($event->new_card_heading) && $event->relationLoaded('eventType') && $event->eventType !== null) {
            $groupType = $event->eventType->group_type;

            $event->new_card_heading = match ($groupType) {
                EventType::TYPE_FIRE => $this->getFireHeading($event),
                EventType::TYPE_RETREAT => '4-Day Retreat',
                EventType::TYPE_EXPERIENCE => '6 Session Course',
                default => null,
            };
        }
    }

    protected function getFireHeading(Event $event): string
    {
        if ($event->frequency) {
            return $event->frequency->label() . ' Gathering';
        }

        return 'Monthly Gathering';
    }

    public function created(Event $event): void
    {
        Artisan::call('app:clear-event-cache');

        if ($event->eventType === null) {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'Event created without an event type; skipping notifications and WeGive tagging',
                properties: ['event_id' => $event->id],
                causedBy: $event->user,
                performedOn: $event,
            );

            return;
        }

        SendNewEventSlackAlert::dispatch($event);

        $this->sendWelcomeEmailWhenLive($event);

        if ($event->eventType->wegive_tag) {
            Bus::chain([
                new TagDonorJob(
                    taggable: $event->user,
                    weGiveTagId: $event->eventType->wegive_tag,
                ),
            ])->dispatch();
        } else {
            LogActivityJob::dispatch(
                logName: WeGiveConnector::ERROR_LOG_NAME,
                description: 'No WeGive tag configured for event type',
                properties: [
                    'event_type' => $event->eventType->name,
                    'event_id' => $event->id,
                ],
                causedBy: $event->user,
                performedOn: $event,
            );
        }
    }

    public function updated(Event $event): void
    {
        Artisan::call('app:clear-event-cache');

        if ($event->wasChanged('approved_at')) {
            $this->sendWelcomeEmailWhenLive($event);
        }
    }

    /**
     * The welcome ("… is live" / "Important details about your upcoming …") email goes out the
     * first time an event is published. Drafts are created long before they're live, so this can't
     * key off creation; welcome_sent_at (set once the mail sends) stops re-publishing from resending.
     */
    protected function sendWelcomeEmailWhenLive(Event $event): void
    {
        if ($event->approved_at === null || $event->welcome_sent_at !== null) {
            return;
        }

        $eventType = $event->eventType;

        if ($eventType === null || ! NewEventCreatedMailable::hasTemplateFor($eventType->name)) {
            return;
        }

        Mail::to($event->user)->send(new NewEventCreatedMailable($event));
    }

    public function deleted(Event $event): void
    {
        Artisan::call('app:clear-event-cache');
    }

    public function forceDeleted(Event $event): void
    {
        Artisan::call('app:clear-event-cache');
    }
}
