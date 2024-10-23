<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Statamic\Statamic;
use Statamic\Facades\Collection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::saving(function (Event $event): void {
            $event->load('host', 'eventType');
        });

        Gate::define('viewPulse', function ($user) {
            return $user->hasRole(User::ROLE_ADMIN);
        });

        Statamic::vite('app', [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ]);

        QueryBuilder::macro(
            'whereNotEmpty',
            function (string $column) {
                return $this->whereNotNull($column)
                    ->where($column, '<>', '');
            }
        );

        EloquentBuilder::macro(
            'whereNotEmpty',
            function (string $column) {
                return $this->getQuery()
                    ->whereNotEmpty($column);
            }
        );


        Collection::computed('media_item', 'custom_url', function ($entry, $value) {

            if (config('features.is_custom_uri_enabled') && $entry->series) {
                $series = $entry->series->value();
                if ($series) {
                    $seriesUri = $series->uri();
                    return $seriesUri . '/' . $entry->slug;
                }
            }
            return 'media' . '/' . $entry->slug();
        });
    }
}
