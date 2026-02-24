<?php

namespace App\Console\Commands\WeGive;

use Exception;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Console\Command;
use App\Jobs\WeGive\TagDonorJob;
use App\Http\Integrations\WeGive\WeGiveConnector;

class AddWeGiveTagsToExistingManagers extends Command
{
    protected $signature = 'wegive:add-tags:existing-managers';

    protected $description = 'Adds WeGive tags to existing event co-hosts/managers';

    public function handle(): int
    {
        $this->info('Starting to tag existing co-hosts in WeGive...');

        $totalTagged = 0;

        Event::with(['managers', 'eventType'])
            ->chunk(100, function ($events) use (&$totalTagged) {
                foreach ($events as $event) {
                    foreach ($event->managers as $manager) {
                        try {
                            TagDonorJob::dispatch(
                                taggable: $manager,
                                weGiveTagId: $event->eventType->wegive_tag
                            );

                            $totalTagged++;

                            $this->comment("Tagged manager {$manager->fullname} for event: {$event->name}");
                        } catch (Exception $e) {
                            dispatch(
                                new LogActivityJob(
                                    logName: WeGiveConnector::ERROR_LOG_NAME,
                                    description: 'AddWeGiveTagsToExistingManagers Error',
                                    properties: [
                                        'manager_id' => $manager->id,
                                        'event_id' => $event->id,
                                        'error' => $e->getMessage(),
                                    ]
                                )
                            );

                            $this->error("Failed to tag manager {$manager->fullname}: {$e->getMessage()}");
                        }
                    }
                }
            });

        $this->info("Successfully queued {$totalTagged} co-host tagging jobs.");

        return 0;
    }
}
