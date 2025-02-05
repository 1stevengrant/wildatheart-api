<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Entries\Collection;

class UpdateEntryUris extends Command
{
    protected $signature = 'app:update-entry-uris';

    protected $description = 'Update the URIs for all entries in all collections';

    public function handle()
    {
        $collections = Collection::all();

        foreach ($collections as $collection) {
            $this->info('Updating URIs for collection: ' . $collection->handle());
            $collection->updateEntryUris();
        }
    }
}
