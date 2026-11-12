<?php

namespace App\Jobs\Admin;

use App\Models\User;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use App\Support\EventSlackMessage;
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

        SlackAlert::to('events')->blocks(EventSlackMessage::userInterest($this->user, $this->event));
    }
}
