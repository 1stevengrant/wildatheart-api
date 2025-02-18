<?php

namespace Ghijk\WildAtHeart;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public const WILD_AT_HEART_PERMISSION = 'wild-at-heart-permission';

    public function bootAddon()
    {
        parent::bootAddon();
        $this->setNav();
        $this->setPermissions();
    }

    private function setNav(): void
    {
        Nav::extend(function ($nav) {
            $nav->content('Wild at Heart')
                ->section('Tools')
                ->can(self::WILD_AT_HEART_PERMISSION)
                ->route('wild-at-heart.index')
                ->icon('cog');
        });
    }

    private function setPermissions(): void
    {
        Permission::group('wild-at-heart', 'Wild at Heart', function () {
            Permission::register(self::WILD_AT_HEART_PERMISSION)
                ->label('Access the Wild at Heart utilities');
        });
    }
}
