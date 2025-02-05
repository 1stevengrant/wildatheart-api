<?php

namespace App\Providers;

use Statamic\Statamic;
use Statamic\Facades\Collection;
use Statamic\Events\AssetUploaded;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Assets\GenerateColorPalette;
use Illuminate\Support\Facades\Event as LaravelEvent;

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

        Collection::computed('media_item', 'custom_url', function ($entry, $value) {

            if ($entry->series) {
                $series = $entry->series->value();
                if ($series) {
                    $seriesUri = $series->uri();

                    return $seriesUri.'/'.$entry->slug;
                }
            }

            return 'media'.'/'.$entry->slug();
        });

        Collection::computed('blogs', 'blog_url', function ($entry, $value) {

            if ($entry->author) {

                $author = $entry->author;
                if ($author) {
                    // Most contributor slugs are using firstname-lastname slug
                    // and we only need the first name for now.
                    $authorSlug = explode('-', $author->slug)[0];

                    return 'blogs/'.$authorSlug.'/'.$entry->slug;
                }
            }

            // If there is no author, we will use the slug as is.
            return 'blogs/'.$entry->slug;
        });

        Statamic::vite('app', [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ]);
    }
}
