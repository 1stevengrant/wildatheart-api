<?php

namespace App\Providers;

use App\Models\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::saving(function (Event $event): void {
            $event->load('host', 'eventType');
        });
    }
}
