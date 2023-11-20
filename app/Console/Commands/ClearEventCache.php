<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearEventCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-event-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the API event cache';

    public function handle(): void
    {
        cache()->forget('events');

        $this->info('Event cache cleared!');
    }
}
