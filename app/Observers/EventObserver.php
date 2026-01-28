<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\EventType;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\Event\SendNewEventSlackAlert;
use App\Mail\Event\NewEventCreatedMailable;

class EventObserver
{
    public function saving(Event $event): void
    {
        // Auto-populate new_card_heading if not explicitly set
        if (empty($event->new_card_heading) && $event->relationLoaded('eventType')) {
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
        SendNewEventSlackAlert::dispatch($event);

        Mail::to($event->user)->send(new NewEventCreatedMailable($event));

        Artisan::call('app:clear-event-cache');

        Bus::chain([
            new TagDonorJob(
                taggable: $event->user,
                weGiveTagId: $event->eventType->wegive_tag,
            ),
        ])->dispatch();
    }

    public function updated(Event $event): void
    {
        Artisan::call('app:clear-event-cache');
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
