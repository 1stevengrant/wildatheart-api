<?php

namespace App\Providers;

use App\Models\Event;
use Statamic\Statamic;
use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const string HOME = '/';

    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Statamic::vite('app', [
            'resources/js/cp.js',
        ]);

        Event::saving(function (Event $event): void {
            $event->load('user', 'eventType');
        });

        Gate::define('viewPulse', function ($user) {
            return $user->hasRole(UserRoleEnum::Admin->value);
        });

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

        $this->bootEvent();
        $this->bootRoute();
    }

    public function bootEvent(): void {}

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('connect', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
