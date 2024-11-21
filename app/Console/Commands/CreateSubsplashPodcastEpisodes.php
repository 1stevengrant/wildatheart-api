<?php

namespace App\Console\Commands;

use App\Services\SubsplashService;
use Illuminate\Console\Command;
use Statamic\Entries\Collection;
use Statamic\Facades\Entry;

class CreateSubsplashPodcastEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-subsplash-podcast-episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create podcast entries in Statamic using Subsplash data';

    public function handle(): void
    {
        $podcasts = Entry::query()
            ->where('collection', 'podcasts')
            ->get();

        $this->updatePodcastSeriesIds($podcasts);

        foreach ($podcasts as $podcast) {
            $this->info("Processing podcast {$podcast->title}");
            $subsplash = new SubsplashService;
            $subsplashSeries = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$podcast->subsplash_series_id}");
            $subsplashMediaItems = $subsplash->getAllRelatedMediaItems($podcast->subsplash_series_id);

            $collection = collect($subsplashMediaItems);
            $chunks = $collection->chunk(100);

            $bar = $this->output->createProgressBar($chunks->count());
            $chunks->each(function ($chunk) use ($bar) {
                $bar->advance();
                $chunk->each(function ($book) {
                    $this->createPodcastEpisodeEntry($book);
                });
            });

            Collection::findByHandle("podcasts")->updateEntryUris();
            Collection::findByHandle("media_item")->updateEntryUris();

            $bar->finish();
        }
    }

    protected function updatePodcastSeriesIds($podcasts): void
    {
        foreach ($podcasts as $podcast) {
            if ($podcast->id !== $podcast->subsplash_series_id) {
                $this->info("Updating podcast series id for {$podcast->title}");
                $duplicate = Entry::make()
                    ->collection('podcasts')
                    ->published(true)
                    ->slug($podcast->slug)
                    ->id($podcast->subsplash_series_id);

                $duplicate->data($podcast->data()->all());
                $duplicate->save();

                $podcast->delete();
            }
        }
    }

    protected function createPodcastEpisodeEntry(array $media): void
    {

        if (! data_get($media, 'id')) {
            $this->info("Skipping media item without id");
            return;
        }

        $title = data_get($media, 'title');


        $entry = Entry::query()
            ->where('collection', 'media_item')
            ->where('id', data_get($media, 'id'))
            ->first();

        if (! $entry) {
            $this->info("Creating entry for {$title}");
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_item')
                ->collection('media_item')
                ->id(data_get($media, 'id'))
                ->slug(data_get($media, 'title'));
        } else {
            $this->info("Updating entry for {$title}");
        }

        $publishedAt = data_get($media, 'published_at');
        $entry->date(\Carbon\Carbon::parse($publishedAt));

        $entry->slug(data_get($media, 'title'));

        $data = [
            'title' => data_get($media, 'title'),
            'subsplash_media_item_id' => data_get($media, 'id'),
        ];

        $seriesId = data_get($media, '_embedded.media-series.id');
        if ($seriesId) {
            $data['series'] = "entry::$seriesId";
        }

        $entry->data($data);
        $entry->save();
    }
}
