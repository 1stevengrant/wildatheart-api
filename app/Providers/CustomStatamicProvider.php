<?php

namespace App\Providers;

use Statamic\Statamic;
use Illuminate\Http\Request;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Collection;
use Statamic\Events\AssetUploaded;
use App\Builders\EntryQueryBuilder;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Assets\GenerateColorPalette;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;
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

        LaravelEvent::listen(function (EntrySaved $event): void {
            if ($event->entry->collection->handle === 'prayers') {
                TriggerAstroBuildJob::dispatch();
            }
        });

        Collection::computed('media_item', 'custom_url', function ($entry, $value) {
            /**
             * Experience Sessions are presented sequentially in this format:
             * /experience/{EXPERIENCE_SLUG}/session-{POSITION_OF_ITEM_IN_EXPERIENCE}
             */
            if ($entry->blueprint->handle === 'experience_session') {
                $experience = EntryQueryBuilder::experiences()
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
                    $podcast = EntryQueryBuilder::podcasts()
                        ->where('data->subsplash_series_id', $series->id)
                        ->first();

                    if ($podcast) {
                        return $podcast->uri() . '/' . $entry->slug;
                    }
                    $seriesUri = $series->uri();

                    return $seriesUri . '/' . $entry->slug;
                }
            }

            return "media/{$entry->slug()}";
        });

        Collection::computed('blogs', 'blog_url', function ($entry, $value) {
            if ($entry->author) {
                $authorSlug = explode('-', $entry->author->slug)[0];

                return "blogs/{$authorSlug}/{$entry->slug}";
            }

            return "blogs/{$entry->slug}";
        });
        Collection::computed('prayers', 'site_url', function ($entry, $value) {
            return mb_trim(config('app.frontend_url'), '/');
        });

        Collection::computed('events', 'site_url', function ($entry, $value) {
            return mb_trim(config('app.frontend_url'), '/');
        });

        $this->overrideElevatedSessionMacro();

        Statamic::vite('app', [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ]);
    }

    /**
     * Override Statamic's hasElevatedSession macro to bypass elevated session
     * checks when disabled via config. This covers both the RequireElevatedSession
     * middleware and CpController::requireElevatedSession().
     *
     * @see https://github.com/statamic/cms/pull/14464
     */
    private function overrideElevatedSessionMacro(): void
    {
        if (config('statamic.users.elevated_sessions_enabled', true)) {
            return;
        }

        Request::macro('hasElevatedSession', function () {
            return true;
        });
    }
}
