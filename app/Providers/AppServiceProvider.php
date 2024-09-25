<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Statamic\Statamic;

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

        //        Model::preventLazyLoading(! app()->isProduction());

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
    }
}
