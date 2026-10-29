<?php

namespace App\Providers;

use App\Models\Event;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\Subsplash\SubsplashApiClient;
use App\Mail\Transport\CloudflareFailoverTransport;
use App\Services\Subsplash\SubsplashMediaItemImporter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Toitzi\LaravelCloudflareMail\CloudflareTransportFactory;

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
    public function register(): void
    {
        $this->app->singleton(SeoService::class);
        $this->app->singleton(SubsplashApiClient::class);
        $this->app->singleton(SubsplashMediaItemImporter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Event::saving(function (Event $event): void {
            $event->load('user', 'eventType');
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

        $this->bootRoute();
        $this->bootMail();
    }

    public function bootMail(): void
    {
        $cloudflareTransportFactory = $this->app->make(CloudflareTransportFactory::class);

        Mail::extend('cloudflare', function (array $config = []) use ($cloudflareTransportFactory): CloudflareFailoverTransport {
            $inner = $cloudflareTransportFactory->make($config);

            return new CloudflareFailoverTransport($inner);
        });
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('connect', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('interestsRead', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('interestsWrite', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('userProfile', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
