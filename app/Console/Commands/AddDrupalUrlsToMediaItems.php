<?php

namespace App\Console\Commands;

use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use App\Services\SubsplashService;

// This is a mess, but it's a one off command that fixes the stuff thats in there now.
class AddDrupalUrlsToMediaItems extends Command
{
    protected $signature = 'app:add-drupal-urls-to-media-items';

    protected $description = 'Updates drupal_url field in subsplash_media_items using source_data.url from audio.json and video.json';

    public function handle()
    {
        $subsplash = new SubsplashService;

        $this->info('Starting to update drupal_url for media items...');
        $allMediaData = $this->getAllMediaData();

        // Track statistics
        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        $deletedItems = [];
        $skippedItems = [];

        // Create a progress bar
        $bar = $this->output->createProgressBar(count($allMediaData));
        $bar->start();

        foreach ($allMediaData as $mediaId => $mediaItem) {
            $bar->advance();

            // Skip if there's no dest_id or source_data.url
            if (! isset($mediaItem['dest_id']) || ! isset($mediaItem['source_data']['url'])) {
                $id = $mediaItem['id'];
                $this->error("dest_id or source_data.url missing for $id");
                $skippedItems[] = $id;
                $skipped++;

                continue;
            }

            $destId = $mediaItem['dest_id'];
            $drupalUrl = $mediaItem['source_data']['url'];

            try {
                // Check if the entry exists
                $entry = Entry::query()
                    ->where('collection', 'media_item')
                    ->where('blueprint', 'subsplash_media_item')
                    ->where('id', $destId)
                    ->first();

                if (! $entry) {
                    $mediaItem = $subsplash->getMediaItem($destId);
                    if (data_get($mediaItem, 'id')) {
                        $this->error("No entry found for: $destId");
                        $notFound++;
                    }
                    $this->error("\nSubsplash media item with id: $destId no longer exists. Skipping.");
                    $deletedItems[] = $destId;
                    $skipped++;

                    continue;
                }

                // Update drupal_url in the data array
                $entry->set('drupal_url', $drupalUrl);
                $entry->save();

                $updated++;
            } catch (\Exception $e) {
                $this->error('Error updating entry ' . $destId . ': ' . $e->getMessage());
                $skipped++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Output summary
        foreach ($deletedItems as $i => $item) {
            $mediaData = collect($allMediaData)->firstWhere('dest_id', $item);
            $title = data_get($mediaData, 'source_data.title', $item);
            $drupalUrl = data_get($mediaData, 'source_data.url', $item);

            $newTitle = $this->cleanTitle($title);

            $this->info("searching for $newTitle");
            $response = $subsplash->findMediaItemByTitle($newTitle);
            $mediaItems = data_get($response, '_embedded.media-items');

            if ($mediaItems) {
                $newMediaItem = collect($mediaItems)->first(function ($item) {
                    return str_contains($item['title'], '(2012)');
                });

                $newMediaItemId = data_get($newMediaItem, 'id');

                if ($newMediaItemId) {

                    $entry = Entry::query()
                        ->where('collection', 'media_item')
                        ->where('blueprint', 'subsplash_media_item')
                        ->where('id', $newMediaItemId)
                        ->first();

                    if (! $entry) {
                        $this->error("no entry found for id $newMediaItemId");
                    }

                    $entry->set('drupal_url', $drupalUrl);
                    $entry->save();
                    $updated++;
                    $skipped--;

                    unset($deletedItems[$i]);
                    $this->info("updated $newMediaItemId with $drupalUrl");
                }

                // $this->info("'$item' => '$newMediaItemId'");
            } else {
                $this->info("nothing found for $newTitle");
            }
            dump($mediaItem);
        }

        foreach ($deletedItems as $item) {
            $this->error("Missing $item");
        }

        foreach ($skippedItems as $item) {
            $this->error("Skipped $item");
        }
        $this->info("\nUpdate complete!");
        $this->info('Updated: ' . $updated . ' entries');
        $this->info('Not found: ' . $notFound . ' entries');
        $this->info('Skipped: ' . $skipped . ' entries');

        return Command::SUCCESS;
    }

    private function removePrefix(string $title)
    {
        $explodedTitle = explode(' - ', $title);

        return $explodedTitle[count($explodedTitle) - 1];
    }

    private function removeAorAn(string $title)
    {
        // Remove leading "A " or "An " from the title using regex
        return preg_replace('/^(A|An|The) /i', '', $title);
    }

    private function cleanTitle(string $title)
    {
        $titleWithoutPrefix = $this->removePrefix($title);
        $titleWithoutArticle = $this->removeAorAn($titleWithoutPrefix);

        return $titleWithoutArticle;
    }

    private function getAllMediaData()
    {
        // Load audio.json and video.json files
        $audioJsonPath = storage_path('app/public/audio.json');
        $videoJsonPath = storage_path('app/public/video.json');

        $audioData = [];
        $videoData = [];

        if (file_exists($audioJsonPath)) {
            $this->info('Loading audio.json...');
            $audioData = json_decode(file_get_contents($audioJsonPath), true);
            $this->info('Loaded ' . count($audioData) . ' audio items.');
        } else {
            $this->error('audio.json not found at ' . $audioJsonPath);
        }

        if (file_exists($videoJsonPath)) {
            $this->info('Loading video.json...');
            $videoData = json_decode(file_get_contents($videoJsonPath), true);
            $this->info('Loaded ' . count($videoData) . ' video items.');
        } else {
            $this->error('video.json not found at ' . $videoJsonPath);
        }

        // Combine both data sources
        $allMediaData = array_merge($audioData, $videoData);
        $this->info('Combined total: ' . count($allMediaData) . ' media items.');

        return $allMediaData;
    }
}
