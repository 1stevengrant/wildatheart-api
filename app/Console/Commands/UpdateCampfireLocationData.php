<?php

namespace App\Console\Commands;

use App\Jobs\LookupLocation;
use App\Models\Campfire;
use Illuminate\Console\Command;

class UpdateCampfireLocationData extends Command
{
    protected $signature = 'app:update-campfire-location-data';

    protected $description = 'Update campfire location data from Mapbox.';

    public function handle(): void
    {
        $campfires = Campfire::all();

        $bar = $this->output->createProgressBar($campfires->count());

        $this->info($campfires->count().' campfires to update');

        $campfires->each(function ($campfire) use ($bar) {
            LookupLocation::dispatch($campfire);
            $bar->advance();
        });

        $bar->finish();
    }
}
