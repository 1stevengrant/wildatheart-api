<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearCampfireCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-campfire-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the API campfire cache';

    public function handle(): void
    {
        cache()->forget('campfires');

        $this->info('Campfire cache cleared!');
    }
}
