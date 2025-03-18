<?php

namespace App\Console\Commands\User;

use App\Models\Event;
use App\Jobs\TagWeGiveDonorJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AddWeGiveTagsToExistingHosts extends Command
{
    protected $signature = 'wegive:add-tags:existing-hosts';

    protected $description = 'Command description';

    public function handle(): int
    {

        Event::with('user')->chunk(100, function ($events) {
            foreach ($events as $event) {
                try {

                    $user = $event->user;

                    if (! $user) {
                        Log::warning("Event ID {$event->id} has no associated user");

                        continue;
                    }

                    if ($user->hasCompleteProfile()) {
                        TagWeGiveDonorJob::dispatch($event);
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
                } catch (\Exception $e) {
                    Log::error("Error processing event ID {$event->id}: {$e->getMessage()}");
                }
            }
        });

        return 0;
    }
}
