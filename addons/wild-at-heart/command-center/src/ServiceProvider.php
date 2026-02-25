<?php

namespace WildAtHeart\CommandCenter;

use Statamic\Facades\User;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon(): void
    {
        parent::bootAddon();
        $this->setNav();
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
        });
    }
}
