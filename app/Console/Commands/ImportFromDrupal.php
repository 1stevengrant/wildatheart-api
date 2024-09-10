<?php

namespace App\Console\Commands;

use App\Jobs\ImportEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportFromDrupal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-from-drupal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import events from the Allies Network to Local platform';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->import();
    }

    public function import(): void
    {
        // filter events with first and last name
        $drupalEvents = collect(Http::get('https://wildatheart.org/json/allies')->json())
            ->filter(fn ($experience) => $experience['first_name'] && $experience['last_name'])
            ->toArray();

        $this->output->progressStart(count($drupalEvents));

        collect($drupalEvents)
            ->each(function ($experience) {

                $this->info('Importing '.$experience['title']);
                $this->line('');
                dispatch(new ImportEvent($experience));

                $this->output->progressAdvance();
            });

        $this->output->progressFinish();
    }
}
