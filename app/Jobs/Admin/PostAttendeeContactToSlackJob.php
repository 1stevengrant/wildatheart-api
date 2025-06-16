<?php

namespace App\Jobs\Admin;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\SlackAlerts\Facades\SlackAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
            ->message(
                (app()->isProduction() ? '' : '🚧 [TEST] ') .
                $this->attendee->fullname . ' contacted ' .
                $this->event->user->fullname . ' about their ' .
                Str::singular($this->event->eventType->name) . ' in ' .
                $this->event->location . ' !'
            );

    }
}
