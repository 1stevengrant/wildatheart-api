<?php

namespace App\Providers;

use Statamic\Statamic;
use Illuminate\Http\Request;
use Statamic\Facades\Search;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Collection;
use Statamic\Events\EntryDeleted;
use Statamic\Events\AssetUploaded;
use App\Support\Search\DatabaseIndex;
use App\Exceptions\CpExceptionHandler;
use App\Support\MediaItemRouteLookups;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Assets\GenerateColorPalette;
use Statamic\Http\Resources\API\Resource;
use Illuminate\Queue\Events\JobProcessing;
use App\Http\Resources\PublicEntryResource;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;
use Statamic\Http\Resources\API\EntryResource;
use Illuminate\Support\Facades\Event as LaravelEvent;
use Statamic\Exceptions\ControlPanelExceptionHandler;

class CustomStatamicProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ControlPanelExceptionHandler::class, CpExceptionHandler::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Resource::map([EntryResource::class => PublicEntryResource::class]);

        Search::extend('database', fn ($app, array $config, string $name, ?string $locale) => new DatabaseIndex($name, $config, $locale));

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

        LaravelEvent::listen([EntrySaved::class, EntryDeleted::class], function (EntrySaved|EntryDeleted $event): void {
            if (in_array($event->entry->collectionHandle(), ['experiences', 'podcasts', 'media_item'])) {
                MediaItemRouteLookups::flush();
            }
        });

        LaravelEvent::listen(JobProcessing::class, fn () => MediaItemRouteLookups::flush());

        Collection::computed('media_item', 'custom_url', function ($entry, $value) {
            /**
             * Experience Sessions are presented sequentially in this format:
             * /experience/{EXPERIENCE_SLUG}/session-{POSITION_OF_ITEM_IN_EXPERIENCE}
             */
            if ($entry->blueprint->handle === 'experience_session') {
                $experience = MediaItemRouteLookups::experienceForSession($entry->id());

                if ($experience) {
                    $sessionNumber = MediaItemRouteLookups::sessionNumber($experience, $entry->id());

                    return "{$experience->uri()}/session-{$sessionNumber}";
                }
            }

            if ($entry->series) {
                $series = $entry->series->value();

                if ($series) {
                    $podcast = MediaItemRouteLookups::podcastForSeries($series->id());

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
