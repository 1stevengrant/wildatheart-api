<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubsplashService;

class GetLatestSubsplashMedia extends Command
{
    protected $signature = 'app:get-latest-subsplash-media {--id= : Process a specific media item by ID}';

    protected $description = 'Imports latest Subplash items.';

    protected $full = false;

    protected function configure()
    {
        $this->addOption('full', null, null, 'Perform a full import of all media items');
    }

    public function __construct(private SubsplashService $subsplash)
    {
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
            $itemsPerPage = 500;
            $nextPage = "https://core.subsplash.com/media/v1/media-items?filter%5Bstatus%5D=published&filter%5Bapp_key%5D=5289PV&page%5Bsize%5D=$itemsPerPage";

            // Loop through all paginated responses.
            while ($nextPage !== null) {
                $totalMediaItems = $this->updateOrCreateEntrys($nextPage, $totalMediaItems);

                // Only process the first batch if 'full' argument
                // was not provided.
                if (! $this->option('full')) {
                    $nextPage = null;
                }
            }

            $this->info("Retreived $totalMediaItems items.");
        } catch (\Exception $e) {
            $this->error('Problem running command:');
            $this->error($e->getMessage());
        }
    }

    private function updateOrCreateEntrys(&$nextPage, $totalMediaItems)
    {
        // Retrieve items on current page.
        $subsplashResponse = $this->subsplash->subsplashFetch($nextPage);
        $mediaItemsInResponse = data_get($subsplashResponse, '_embedded.media-items');

        // Update next page if available.
        $nextPage = data_get($subsplashResponse, '_links.next.href', null);

        // Increment the total item counter.
        $totalMediaItems = $totalMediaItems + count($mediaItemsInResponse);

        if ($errors = data_get($subsplashResponse, 'errors')) {
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        // Update or create entries for all media items.
        $bar = $this->output->createProgressBar($totalMediaItems);
        $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
        $bar->setProgress($totalMediaItems - count($mediaItemsInResponse));

        $bar->start();

        foreach ($mediaItemsInResponse as $key => $item) {
            // Optional: Display current item in progress bar
            $bar->setMessage("Processing: {$item['title']}");

            $this->subsplash->saveMediaItemAsEntry($item);
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
            $mediaItem = $this->subsplash->getMediaItem($mediaItemId);

            if (isset($mediaItem['errors'])) {
                $this->error('Error fetching media item:');
                foreach ($mediaItem['errors'] as $error) {
                    $this->error($error);
                }
                return;
            }

            if (!isset($mediaItem['id'])) {
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
            $entry = $this->subsplash->saveMediaItemAsEntry($mediaItem);
            $bar->advance();

            $bar->setMessage('Processing complete.');
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
