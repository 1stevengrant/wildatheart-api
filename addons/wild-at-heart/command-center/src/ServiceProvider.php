<?php

namespace WildAtHeart\CommandCenter;

use Statamic\Facades\User;
use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Statamic\Providers\AddonServiceProvider;
use WildAtHeart\CommandCenter\Widgets\AskPlatform;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $widgets = [
        AskPlatform::class,
    ];

    public function bootAddon(): void
    {
        parent::bootAddon();
        $this->setNav();
        $this->defineAskPlatformAccess();
    }

    private function setNav(): void
    {
        Nav::extend(function ($nav) {
            $user = User::current();

            if (! $user?->isSuper()) {
                return;
            }

            $nav->content('Command Center')
                ->section('Tools')
                ->route('command-center.index')
                ->icon('dashboard');

            $nav->content('Ask the Platform')
                ->section('Tools')
                ->route('command-center.ask.index')
                ->icon('ai-spark');
        });
    }

    private function defineAskPlatformAccess(): void
    {
        Gate::define('usePlatformAnalyst', fn (UserModel $user) => $user->isSuperAdmin());

        RateLimiter::for('ask-platform', fn (Request $request) => [
            Limit::perMinute(20)->by("minute:{$request->user()?->id}"),
            Limit::perDay(200)->by("day:{$request->user()?->id}"),
        ]);
    }
}
