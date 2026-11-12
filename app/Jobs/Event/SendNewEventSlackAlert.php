<?php

namespace App\Jobs\Event;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use App\Support\EventSlackMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\SlackAlerts\Facades\SlackAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNewEventSlackAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Event $event) {}

    public function handle(): void
    {
        if (! config('slack-alerts.slack_alerts_enabled')) {
            return;
        }

        SlackAlert::to('events')->blocks(EventSlackMessage::newEvent($this->event));
    }
}
