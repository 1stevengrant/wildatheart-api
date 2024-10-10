<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Statamic\Assets\Asset;
use Statamic\Eloquent\Entries\Entry;

class ImportPrayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-prayers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import prayers from Drupal into Statamic';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->import();
    }

    public function import(): void
    {
        $drupalPrayers = collect(Http::get('https://wildatheart.org/json/prayer')->json())
            ->toArray();

        $this->output->progressStart(count($drupalPrayers));

        collect($drupalPrayers)
            ->each(function ($prayer) {

                $this->info('Importing '.$prayer['title']);
                $this->line('');

                $existingPrayer = Entry::query()
                    ->where('collection', 'prayers')
                    ->where('data->drupal_url', str_replace('https://wildatheart.org', '', $prayer['url']))
                    ->first();

                // Prepare the new data for comparison
                $newData = [
                    'title' => $prayer['title'],
                    'content' => $this->getBody($prayer['body']),
                    'summary' => $prayer['summary'],
                    'drupal_url' => str_replace('https://wildatheart.org', '', $prayer['url']),
                ];

                // Import PDF from URL into Statamic assets
                if (! empty($prayer['pdf_link'])) {
                    $newData['pdf'] = $this->importFile($prayer['pdf_link']);
                }

                // Import MP3 from URL into Statamic assets
                if (! empty($prayer['audioUrl'])) {
                    $newData['mp3'] = $this->importFile($prayer['audioUrl']);
                }

                if ($existingPrayer) {
                    $existingPrayer->data($newData);
                    $existingPrayer->save();
                } else {
                    $entry = Entry::make()
                        ->published(true)
                        ->blueprint('prayers')
                        ->collection('prayers')
                        ->slug($prayer['title'])
                        ->data($newData);
                    $entry->save();
                }
                $this->output->progressAdvance();
            });

        $this->output->progressFinish();
    }

    public function getBody($body): string
    {
        return $body;
    }

    public function importFile($srcUrl)
    {
        $filename = basename($srcUrl);

        $existingAsset = Asset::find("s3::files/prayers/{$filename}");

        if ($existingAsset) {
            return $existingAsset->path;
        }
        $fileContent = Http::get($srcUrl)->body();
        $tempFile = tempnam(sys_get_temp_dir(), 'prayer_file_');
        file_put_contents($tempFile, $fileContent);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            $filename,
            'application/pdf',
            null,
            true
        );

        if ($uploadedFile) {
            $asset = Asset::make()->container('s3')->path("files/prayers/{$filename}");
            $asset->upload($uploadedFile);
            $asset->save();

            // Add the asset to the prayer data
            return $asset->path;
        }
    }
}
