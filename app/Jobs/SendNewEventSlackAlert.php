<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SendNewEventSlackAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Event $event) {}

    public function handle(): void
    {
        if (! config('slack-alerts.slack_alerts_enabled')) {
            return;
        }

        $event = $this->event;
        $hostName = $event->host->fullName;
        $location = $event->location;
        $name = $event->name;

        SlackAlert::to('events')
            ->blocks([
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => 'We have a new '.Str::singular($event->eventType->name).'!',
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Name:*\n $name \n*Where:*\n$location \n*Who:* $hostName ",
                    ],
                    'accessory' => [
                        'type' => 'image',
                        'image_url' => bunnyImage($event->eventType->image),
                        'alt_text' => 'fire thumbnail',
                    ],
                ],
                [
                    'type' => 'actions',
                    'elements' => [
                        [
                            'type' => 'button',
                            'text' => [
                                'type' => 'plain_text',
                                'text' => 'View Event In Admin',
                                'emoji' => true,
                            ],
                            'value' => 'View In Admin',
                            'url' => $event->adminUrl,
                            'action_id' => 'view-in-admin',
                        ],
                        [
                            'type' => 'button',
                            'text' => [
                                'type' => 'plain_text',
                                'text' => 'View Public Listing',
                                'emoji' => true,
                            ],
                            'value' => 'View Public Listing',
                            'url' => route('view.event', $event),
                            'action_id' => 'view-listing',
                        ],
                    ],
                ],
            ]);

    }
}
