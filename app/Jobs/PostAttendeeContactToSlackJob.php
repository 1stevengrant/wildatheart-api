<?php

namespace App\Jobs;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Spatie\SlackAlerts\Facades\SlackAlert;

class PostAttendeeContactToSlackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Attendee $attendee, public Event $event) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! config('slack-alerts.slack_alerts_enabled')) {
            return;
        }

        SlackAlert::to('events')
            ->message(app()->isProduction() ? '' : '🚧 [TEST] '.$this->attendee->fullName.' contacted '
                .$this->event->host->fullName.' about their '.Str::singular($this->event->eventType->name).' in '.$this->event->location.' !');

    }
}
