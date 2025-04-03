<?php

namespace App\Console\Commands\User;

use Exception;
use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Console\Command;
use App\Jobs\WeGive\FindOrCreateDonorJob;
use App\Http\Integrations\WeGive\WeGiveConnector;

class AddWeGiveTagsToExistingHosts extends Command
{
    protected $signature = 'wegive:add-tags:existing-hosts';

    protected $description = 'Adds a WeGive tag to existing event hosts';

    public function handle(): int
    {

        Event::with('user')->chunk(100, function ($events) {
            foreach ($events as $event) {
                try {

                    /** @var User|null $user */
                    $user = $event->user;

                    if (! $user) {

                        dispatch(new LogActivityJob(
                            logName: WeGiveConnector::WARNING_LOG_NAME,
                            description: 'Event has no associated user',
                            properties: [
                                'event_id' => $event->id,
                            ]
                        ));

                        continue;
                    }

                    if ($user->hasCompleteProfile()) {
                        FindOrCreateDonorJob::dispatch($event);
                    } else {
                        $this->table(['ID', 'Given Name', 'Family Name', 'Email', 'Phone'], [
                            [
                                $user->id,
                                $user->given_name,
                                $user->family_name,
                                $user->email,
                                $user->phone,
                            ],
                        ]);
                    }
                } catch (Exception $e) {
                    dispatch(
                        new LogActivityJob(
                            logName: WeGiveConnector::ERROR_LOG_NAME,
                            description: 'AddWeGiveTagsToExistingHosts Error',
                            properties: [
                                'event_id' => $event->id,
                                'user_id' => $user->id ?? null,
                                'error' => $e->getMessage(),
                            ]
                        )
                    );
                    throw $e;
                }
            }
        });

        return 0;
    }
}
