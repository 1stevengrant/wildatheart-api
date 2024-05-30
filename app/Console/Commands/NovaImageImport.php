<?php

namespace App\Console\Commands;

use App\Models\EventType;
use Illuminate\Console\Command;
use Statamic\Facades\Asset;

class NovaImageImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:nova-image-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventTypes = \App\Models\EventType::all();

        $bar = $this->output->createProgressBar($eventTypes->count());

        $eventTypes->each(function (EventType $eventType) use ($bar) {
            if ($eventType->image) {
                $this->info('Importing '.$eventType->name);
                $asset = Asset::make();
                $asset->container('app')->path($eventType->image)->save();
            } else {
                $this->info('No image for '.$eventType->name);
                $this->info('Skipping...');
            }
            $bar->advance();
        });

        $bar->finish();
    }
}
