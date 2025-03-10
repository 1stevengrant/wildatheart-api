<?php

namespace App\Console\Commands;

use App\Services\SubsplashService;
use Illuminate\Console\Command;
use Statamic\Facades\Entry;

class CorrectSubsplashSeries extends Command
{
    protected $signature = 'app:correct-subsplash-series';

    protected $description = 'Command description';

    public function handle()
    {

        // Locate items with missing series
        $entriesWithoutSeries = Entry::query()
            ->where('collection', 'media_item')
            ->where('blueprint', 'subsplash_media_item')
            ->where('data->series', null)
            ->get();

        $this->info("Located " . $entriesWithoutSeries->count() . " items with no series.");
        $this->info("Attempting corrections...");

        $subsplash = new SubsplashService();

        foreach ($entriesWithoutSeries as $entry) {
            // $this->line("Reparing " . $entry->title);
            $subsplashId = $entry->subsplash_media_item_id;
            $seriesId = $subsplash->getMediaSeriesFromItemId($subsplashId);
            if (!$seriesId) {
                $this->newLine();
                $this->error("No Series id found for " . $entry->title);
                continue;
            }
            $seriesEntry = Entry::query()
                ->where('collection', "media_series")
                ->where('id', $seriesId)
                ->first();

            if (!$seriesEntry) {
                $this->error("No series entry found for " . $seriesEntry->title);
                continue;
            }

            $this->info("Attempt to update " . $entry->id);
            $seriesId = $seriesEntry->id;
            $entry->series = "entry::$seriesId";
            $entry->save();
        }
    }
}
