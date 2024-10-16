<?php

namespace App\Console\Commands;

use App\Services\TransistorService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AddShareURLToEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-share-url-to-episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A one off command to add share URLs to episodes';
    /**
     * This command is only here as an example and shouldn't need to be run again.
     */

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->addShareURLToEpisodes();
    }

    public function addShareURLToEpisodes(): void
    {

        $transistorService = new TransistorService;

        $episodes = $transistorService->getAll('wildatheart-daily', 'scheduled');

        $progressBar = $this->output->createProgressBar(count($episodes));
        $progressBar->start();

        $this->info('Adding share URLs to episodes...');

        foreach ($episodes as $episode) {

            $slug = Str::slug($episode['attributes']['title']);
            $this->info($slug);

            $alternate_url = 'https://wildatheart.org/daily-readings/'.$slug;
            $data = [
                'episode' => ['alternate_url' => $alternate_url],
            ];

            $transistorService->updateEpisode($episode['id'], $data);
            sleep(1);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('Share URLs added to episodes.');
    }
}
