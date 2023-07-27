<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;

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
        Component::macro('notify', function ($message, $timeout): void {
            $this->dispatchBrowserEvent('notify', [
                'message' => $message,
                'timeout' => $timeout,
            ]);
        });

        Model::preventLazyLoading(! app()->isProduction());
    }
}
