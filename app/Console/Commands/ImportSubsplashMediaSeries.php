<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Statamic\Facades\Entry;

class ImportSubsplashMediaSeries extends Command
{
    protected $signature = 'app:import-subsplash-media-series';

    protected $description = 'Imports Subsplash Media Series into the Media Series collection.';

    public function handle()
    {
        $jsonData = json_decode(file_get_contents(storage_path('app/public/series.json')), true);
        $collection = collect($jsonData);
        $chunks = $collection->chunk(100);

        $bar = $this->output->createProgressBar($chunks->count());
        $chunks->each(function ($chunk) use ($bar) {
            $bar->advance();
            $chunk->each(function ($book) {
                $this->createEntry($book);
            });
        });

        $bar->finish();
    }

    protected function createEntry(array $media): void
    {

        $entry = Entry::query()
            ->where('collection', 'media_series')
            ->where('id', $media['dest_id'])
            ->first();

        if (!$entry) {
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_series')
                ->collection('media_series')
                ->id($media['dest_id'])
                ->slug($media['source_data']['title']);
        }
        $date = Carbon::now();

        $entry->date($date);

        $entry->data([
            'title' => $media['source_data']['title'],
            'subsplash_media_series_id' => $media['dest_id'],
            'drupal_url' => $media['source_data']['url'],
        ]);

        $entry->save();
    }
}
