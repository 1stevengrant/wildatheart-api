<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Statamic\Facades\Entry;

class ImportSubsplashMediaItems extends Command
{
    protected $signature = 'app:import-subsplash-media-items';

    protected $description = 'Imports Subsplash Media Items into the Media Items collection.';

    public function handle()
    {
        $jsonData = json_decode(file_get_contents(storage_path('app/public/audio.json')), true);
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
        if (!isset($media['dest_id'])) {
            return;
        }

        $entry = Entry::query()
            ->where('collection', 'media_item')
            ->where('id', $media['dest_id'])
            ->first();

        if (!$entry) {
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_item')
                ->collection('media_item')
                ->id($media['dest_id'])
                ->slug($media['source_data']['title']);
        }
        $date = Carbon::now();

        $entry->date($date);
        $entry->slug($media['source_data']['title']);
        $data = [
            'title' => $media['source_data']['title'],
            'subsplash_media_item_id' => $media['dest_id'],
            'drupal_url' => $media['source_data']['url'],
        ];


        if (isset($media['dest_data']) && isset($media['dest_data']["_embedded"]) && isset($media['dest_data']["_embedded"]["media-series"])) {
            $seriesId = $media['dest_data']["_embedded"]["media-series"]["id"];
            $data["series"] = "entry::$seriesId";
        }

        $entry->data($data);

        $entry->save();
    }
}
