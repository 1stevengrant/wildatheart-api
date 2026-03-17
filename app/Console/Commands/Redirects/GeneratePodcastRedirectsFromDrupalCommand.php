<?php

namespace App\Console\Commands\Redirects;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Builders\EntryQueryBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GeneratePodcastRedirectsFromDrupalCommand extends Command
{
    protected $signature = 'app:generate-podcast-redirects';

    protected $description = 'Generates redirects from Drupal podcast pages';

    public function handle()
    {

        $response = Http::get('https://wildatheart.org/json/podcasts');
        $data = $response->json();

        $onlyPodcasts = collect($data)->filter(function ($item) {
            $url = data_get($item, 'url');

            return ! preg_match("/\/rhplay\/[^\/]+$/", $url)
                && ! str_contains($url, 'rhplay/video')
                && ! str_contains($url, 'rhplay/audio');
        });

        if (! $data) {
            $this->error('Failed to fetch podcast data');

            return 1;
        }

        $fileName = $this->getFileName('podcast_redirects.txt');

        Storage::makeDirectory('redirects');

        $missingEntries = [];

        // Discovered a few things that were imported and then deleted.
        $ignoreRoutes = [
            '/psa-back-next-week',
            "John Eldredge Explains Why Understanding Isn't Healing and how Jesus Is Integrating Force of the Universe", // changed title
            '/140-marriage-self-care-and-deeper-questions', // AS episode that appears to have been deleted
        ];

        $fallbackRoutes = [

            '/rhplay/podcast/wild-heart/809-john-eldredge-answers-your-questions-part-2' => '/podcasts/wild-at-heart/e809-john-eldredge-answers-your-questions-part-2',
        ];

        foreach ($onlyPodcasts as $item) {
            $url = data_get($item, 'url');

            $line = $url;
            $line .= ' ';

            $title = data_get($item, 'title');
            $slug = Str::slug($title);
            $entry = EntryQueryBuilder::query()->bySlug($slug)->first();

            if ($entry) {
                $entryTitle = $entry->title;
                $this->info("Found $entryTitle");
                $line .= $entry->uri;
                $line .= ' 301';
                Storage::append($fileName, $line);
            } elseif (in_array($url, $ignoreRoutes)) {
                $this->info("Skipping $title");
            } elseif ($fallback = data_get($fallbackRoutes, $url)) {
                $line .= $fallback;
                $line .= ' 301';
            } else {
                $this->error("Could not find $title");
                $missingEntries[] = $title;
            }
        }

        foreach ($missingEntries as $entry) {
            $this->error($entry);
        }

        $this->info("Written to $fileName");
    }

    private function getFileName(string $fileName)
    {
        $fileDate = now()->format('Ymd');
        $fileDateMillis = round(microtime(true) * 1000);

        return "{$fileDate}_{$fileDateMillis}_{$fileName}";
    }
}
