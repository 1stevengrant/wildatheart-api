<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashMediaItemImporter;

class GetLatestSubsplashMedia extends Command
{
    protected $signature = 'app:get-latest-subsplash-media {--id= : Process a specific media item by ID}';

    protected $description = 'Imports latest Subplash items.';

    protected $full = false;

    protected function configure()
    {
        $this->addOption('full', null, null, 'Perform a full import of all media items');
        $this->addOption('force', null, null, 'Force retrieval regardless of when the most recent change was');
    }

    public function __construct(
        private SubsplashApiClient $apiClient,
        private SubsplashMediaItemImporter $importer,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Fetching items from Subsplash...');
        try {
            // Check if a specific ID was provided
            if ($mediaItemId = $this->option('id')) {
                $this->info("Processing single media item: {$mediaItemId}");
                $this->processSingleMediaItem($mediaItemId);

                return;
            }

            $totalMediaItems = 0;
            $nextPage = $this->apiClient->getPublishedMediaItemsUrl();

            // Loop through all paginated responses.
            while ($nextPage !== null) {
                $totalMediaItems = $this->updateOrCreateEntries($nextPage, $totalMediaItems);

                // Only process the first batch if 'full' argument
                // was not provided.
                if (! $this->option('full')) {
                    $nextPage = null;
                }
            }

            $this->info("Retrieved $totalMediaItems items.");
        } catch (\Exception $e) {
            $this->error('Problem running command:');
            $this->error($e->getMessage());
        }
    }

    private function updateOrCreateEntries(&$nextPage, $totalMediaItems)
    {
        // Retrieve items on current page.
        $subsplashResponse = $this->apiClient->fetch($nextPage);
        $mediaItemsInResponse = data_get($subsplashResponse, '_embedded.media-items', []);

        // Update next page if available.
        $nextPage = data_get($subsplashResponse, '_links.next.href', null);

        if ($errors = data_get($subsplashResponse, 'errors')) {
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        if (empty($mediaItemsInResponse)) {
            $this->warn('No media items found in response.');
            $nextPage = null;

            return $totalMediaItems;
        }

        // Increment the total item counter.
        $totalMediaItems = $totalMediaItems + count($mediaItemsInResponse);

        // Update or create entries for all media items.
        $bar = $this->output->createProgressBar($totalMediaItems);
        $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
        $bar->setProgress($totalMediaItems - count($mediaItemsInResponse));

        $bar->start();

        foreach ($mediaItemsInResponse as $key => $item) {
            // Optional: Display current item in progress bar
            $bar->setMessage("Processing: {$item['title']}");

            $item = $this->apiClient->getMediaItem($item['id']);

            $this->importer->import($item, false, $this->option('force'), function ($status, $title, $currentUpdated, $lastUpdated) {
                if ($status === 'skipped') {
                    $lastUpdatedFormatted = $lastUpdated instanceof \Carbon\Carbon
                        ? $lastUpdated->format('Y-m-d H:i:s')
                        : $lastUpdated;
                    $this->line("<comment>Skipped: {$title} (no changes since {$lastUpdatedFormatted})</comment>");
                }
            });
            $bar->advance();
        }

        $bar->setMessage('Chunk finished.');
        $bar->finish();
        $this->output->newLine(2); // Add some space after the progress bar

        return $totalMediaItems;
    }

    private function processSingleMediaItem(string $mediaItemId): void
    {
        try {
            $this->info("Fetching media item with ID: {$mediaItemId}");

            // Fetch the specific media item from Subsplash
            $mediaItem = $this->apiClient->getMediaItem($mediaItemId);

            if (isset($mediaItem['errors'])) {
                $this->error('Error fetching media item:');
                foreach ($mediaItem['errors'] as $error) {
                    $this->error($error);
                }

                return;
            }

            if (! isset($mediaItem['id'])) {
                $this->error("Media item with ID {$mediaItemId} not found.");

                return;
            }

            $this->info("Processing media item: {$mediaItem['title']}");

            // Create a progress bar for the single item
            $bar = $this->output->createProgressBar(1);
            $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
            $bar->setMessage("Processing: {$mediaItem['title']}");
            $bar->start();

            // Save the media item as an entry
            $entry = $this->importer->import($mediaItem, false, $this->option('force'), function ($status, $title, $currentUpdated, $lastUpdated) {
                if ($status === 'skipped') {
                    $lastUpdatedFormatted = $lastUpdated instanceof \Carbon\Carbon
                        ? $lastUpdated->format('Y-m-d H:i:s')
                        : $lastUpdated;
                    $this->info("<comment>Skipped: {$title} (no changes since {$lastUpdatedFormatted})</comment>");
                }
            });
            $bar->advance();

            // $bar->setMessage('Processing complete.');
            $bar->finish();
            $this->output->newLine(2);

            $this->info("Successfully processed media item: {$mediaItem['title']}");
            $this->info("Entry ID: {$entry->id()}");
        } catch (\Exception $e) {
            $this->error("Error processing media item {$mediaItemId}:");
            $this->error($e->getMessage());
        }
    }
}
