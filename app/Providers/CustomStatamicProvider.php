<?php

namespace App\Providers;

use Statamic\Statamic;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use Statamic\Events\AssetUploaded;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Assets\GenerateColorPalette;
use Ghijk\WildAtHeart\Http\Controllers\Actions\TriggerAstroBuildController;
use Illuminate\Support\Facades\Event as LaravelEvent;
use Statamic\Events\EntrySaved;

class CustomStatamicProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        LaravelEvent::listen(function (AssetUploaded $event): void {
            if ($event->asset->container->handle === 's3') {
                GenerateColorPalette::dispatch(asset: $event->asset);
            }
        });

        LaravelEvent::listen(function (EntrySaved $event): void {

            if ($event->entry->collection->handle === 'prayers') {

                app(TriggerAstroBuildController::class)();
            }
        });

        Collection::computed('media_item', 'custom_url', function ($entry, $value) {

            /**
             * Experience Sessions are presented sequentially in this format:
             * /experience/{EXPERIENCE_SLUG}/session-{POSITION_OF_ITEM_IN_EXPERIENCE}
             */
            if ($entry->blueprint->handle === 'experience_session') {
                $experience = Entry::query()
                    ->where('collection', 'experiences')
                    ->where(function ($query) use ($entry) {
                        $query->whereJsonContains('data->sessions', $entry->id);
                    })
                    ->first();

                if ($experience) {
                    $sessionIndex = $experience->sessions->search(function ($session) use ($entry) {
                        return $session->id === $entry->id;
                    });
                    $sessionIndexPlusOne = $sessionIndex + 1;

                    return $experience->uri() . '/session-' . $sessionIndexPlusOne;
                }
            }

            if ($entry->series) {

                $series = $entry->series->value();

                if ($series) {
                    $podcast = Entry::query()
                        ->where('collection', 'podcasts')
                        ->where('data->subsplash_series_id', $series->id)
                        ->first();

                    if ($podcast) {
                        return $podcast->uri() . '/' . $entry->slug;
                    }
                    $seriesUri = $series->uri();

                    return $seriesUri . '/' . $entry->slug;
                }
            }

            return 'media' . '/' . $entry->slug();
        });

        Collection::computed('blogs', 'blog_url', function ($entry, $value) {

            if ($entry->author) {

                $author = $entry->author;
                if ($author) {
                    // Most contributor slugs are using firstname-lastname slug
                    // and we only need the first name for now.
                    $authorSlug = explode('-', $author->slug)[0];

                    return 'blogs/' . $authorSlug . '/' . $entry->slug;
                }
            }

            // If there is no author, we will use the slug as is.
            return 'blogs/' . $entry->slug;
        });
        Collection::computed('prayers', 'site_url', function ($entry, $value) {
            return trim(config('app.frontend_url'), '/');
        });

        Statamic::vite('app', [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ]);
    }
}
