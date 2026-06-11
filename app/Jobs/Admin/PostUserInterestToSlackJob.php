<?php

namespace App\Jobs\Admin;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\SlackAlerts\Facades\SlackAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PostUserInterestToSlackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user, public Event $event) {}

    public function handle(): void
    {
        if (! config('slack-alerts.slack_alerts_enabled')) {
            return;
        }

        SlackAlert::to('events')
            ->message(
                (app()->isProduction() ? '' : '🚧 [TEST] ') .
                $this->user->fullname . ' contacted ' .
                $this->event->user->fullname . ' about their ' .
                Str::singular($this->event->eventType->name) . ' in ' .
                $this->event->location . ' !'
            );
    }
}
