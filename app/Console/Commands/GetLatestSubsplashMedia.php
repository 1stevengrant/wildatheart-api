<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubsplashService;

class GetLatestSubsplashMedia extends Command
{
    protected $signature = 'app:get-latest-subsplash-media';

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
}
