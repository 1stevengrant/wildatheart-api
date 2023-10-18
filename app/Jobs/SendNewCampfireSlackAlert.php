<?php

namespace App\Jobs;

use App\Models\Campfire;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SendNewCampfireSlackAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campfire $campfire)
    {

    }

    public function handle(): void
    {
        $campfire = $this->campfire;
        $hostName = $campfire->host->fullName;
        $location = $campfire->location;
        $country = $campfire->country;
        $name = $campfire->name;

        SlackAlert::to('events')
            ->blocks([
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => 'We have a new Fire',
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Name:*\n $name Fire \n*Where:*\n$location, $country \n*Who:* $hostName ",
                    ],
                    'accessory' => [
                        'type' => 'image',
                        'image_url' => asset('img/wah-fires-suburb-1.webp'),
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
                                'text' => 'View Fire',
                                'emoji' => true,
                            ],
                            'value' => 'View In Admin',
                            'url' => $campfire->adminUrl,
                            'action_id' => 'view-in-admin',
                        ],
                        [
                            'type' => 'button',
                            'text' => [
                                'type' => 'plain_text',
                                'text' => 'View Listing',
                                'emoji' => true,
                            ],
                            'value' => 'View Listing',
                            'url' => route('view.fire', $campfire),
                            'action_id' => 'view-listing',
                        ],
                    ],
                ],
            ]);
    }
}
