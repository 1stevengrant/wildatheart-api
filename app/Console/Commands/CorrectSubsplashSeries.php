<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Builders\EntryQueryBuilder;
use App\Services\Subsplash\SubsplashApiClient;

class CorrectSubsplashSeries extends Command
{
    protected $signature = 'app:correct-subsplash-series';

    protected $description = 'Command description';

    public function __construct(
        private SubsplashApiClient $apiClient,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {

        // Locate items with missing series
        $entriesWithoutSeries = EntryQueryBuilder::mediaItems()
            ->byBlueprint('subsplash_media_item')
            ->where('data->series', null)
            ->get();

        $this->info('Located ' . $entriesWithoutSeries->count() . ' items with no series.');
        $this->info('Attempting corrections...');

        foreach ($entriesWithoutSeries as $entry) {
            // $this->line("Reparing " . $entry->title);
            $subsplashId = $entry->subsplash_media_item_id;
            $seriesId = $this->apiClient->getMediaSeriesFromItemId($subsplashId);
            if (! $seriesId) {
                $this->newLine();
                $this->error('No Series id found for ' . $entry->title);

                continue;
            }
            $seriesEntry = EntryQueryBuilder::mediaSeries()
                ->find($seriesId);

            if (! $seriesEntry) {
                $this->error('No series entry found for ' . $seriesEntry->title);

                continue;
            }

            $this->info('Attempt to update ' . $entry->id);
            $seriesId = $seriesEntry->id;
            $entry->series = "entry::$seriesId";
            $entry->save();
        }
    }
}
