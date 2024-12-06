<?php

namespace App\Providers;

use App\Models\User;
use Laravel\Nova\Nova;
use AlexAzartsev\Heroicon\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        Nova::footer(static fn ($request): string => Blade::render(''));

        Nova::withBreadcrumbs();

        Heroicon::defaultIconSets(['solid', 'outline']);
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            if ($user->hasRole(User::ROLE_ADMIN)) {
                return true;
            }

            abort(redirect()->route('login'));
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     */
    protected function dashboards(): array
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     */
    public function tools(): array
    {
        return [
            \Vyuldashev\NovaPermission\NovaPermissionTool::make(),

            new \Stepanenko3\NovaCommandRunner\CommandRunnerTool,
        ];
    }

    public function register(): void
    {
        Nova::report(function ($exception) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($exception);
            }
        });
    }
}
