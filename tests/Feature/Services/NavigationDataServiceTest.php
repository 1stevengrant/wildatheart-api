<?php

use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\NavigationDataService;
use App\Exceptions\NavigationUnavailableException;

function setNavigationGlobal(string $handle, array $data): void
{
    $globalSet = GlobalSet::findByHandle($handle);

    $variables = $globalSet->in('default') ?? $globalSet->makeLocalization('default');

    $variables->data($data)->save();
}

function desktopNavFixture(): array
{
    return [
        'desktop_nav' => [
            [
                'id' => 'media',
                'type' => 'main_nav_item',
                'title' => 'Media',
                'enabled' => true,
                'dropdown_links' => [
                    ['id' => 'podcasts', 'title' => 'Podcasts', 'url' => '/podcasts', 'enabled' => true],
                    ['id' => 'videos', 'title' => 'Videos', 'url' => '/videos', 'enabled' => false],
                ],
            ],
            [
                'id' => 'hidden',
                'type' => 'main_nav_item',
                'title' => 'Hidden',
                'enabled' => false,
            ],
        ],
    ];
}

function mobileNavFixture(): array
{
    return [
        'main_nav_bar' => [
            ['id' => 'home', 'title' => 'Home', 'url' => 'https://example.com', 'enabled' => true],
            ['id' => 'off', 'title' => 'Off', 'url' => 'https://example.com/off', 'enabled' => false],
        ],
        'drawer_items' => [
            ['id' => 'events', 'title' => 'Events', 'url' => 'https://example.com/events', 'enabled' => true],
        ],
    ];
}

beforeEach(function () {
    Cache::flush();
});

it('returns enabled desktop items with enabled nested dropdown links', function () {
    setNavigationGlobal('desktop_nav', desktopNavFixture());

    $items = app(NavigationDataService::class)->desktop();

    expect($items)->toHaveCount(1)
        ->and($items[0]['id'])->toBe('media')
        ->and($items[0]['dropdownLinks'])->toHaveCount(1)
        ->and(array_values($items[0]['dropdownLinks'])[0]['id'])->toBe('podcasts');
});

it('returns enabled mobile items for the main bar and drawer', function () {
    setNavigationGlobal('mobile_nav', mobileNavFixture());

    $navigation = app(NavigationDataService::class)->mobile();

    expect(collect($navigation['main_nav_bar'])->pluck('id')->all())->toBe(['home'])
        ->and(collect($navigation['drawer_items'])->pluck('id')->all())->toBe(['events']);
});

it('combines desktop and mobile navigation', function () {
    setNavigationGlobal('desktop_nav', desktopNavFixture());
    setNavigationGlobal('mobile_nav', mobileNavFixture());

    $service = app(NavigationDataService::class);

    expect($service->all())->toBe([
        'desktop' => $service->desktop(),
        'mobile' => $service->mobile(),
    ]);
});

it('uses an empty section in combined navigation when one navigation is unavailable', function () {
    setNavigationGlobal('mobile_nav', mobileNavFixture());

    $navigation = app(NavigationDataService::class)->all();

    expect($navigation['desktop'])->toBe([])
        ->and($navigation['mobile']['drawer_items'])->toHaveCount(1);
});

it('throws and logs when the global set is missing', function () {
    Log::spy();

    GlobalSet::shouldReceive('findByHandle')->with('desktop_nav')->andReturn(null);

    expect(fn () => app(NavigationDataService::class)->desktop())
        ->toThrow(NavigationUnavailableException::class, 'Desktop nav global set not found');

    Log::shouldHaveReceived('warning')->with('Navigation global set not found: desktop_nav');
});

it('throws when the navigation has never been saved', function () {
    expect(fn () => app(NavigationDataService::class)->mobile())
        ->toThrow(NavigationUnavailableException::class);
});

it('throws when the navigation data is empty', function () {
    setNavigationGlobal('mobile_nav', []);

    expect(fn () => app(NavigationDataService::class)->mobile())
        ->toThrow(NavigationUnavailableException::class, 'Navigation data is empty');
});

it('serves cached navigation without reading statamic again', function () {
    setNavigationGlobal('desktop_nav', desktopNavFixture());

    $service = app(NavigationDataService::class);

    $first = $service->desktop();

    GlobalSet::shouldReceive('findByHandle')->never();

    expect($service->desktop())->toBe($first)
        ->and(Cache::get('navigation.desktop_nav.v3'))->toBe($first);
});

it('does not cache unavailable navigation', function () {
    $service = app(NavigationDataService::class);

    expect(fn () => $service->desktop())->toThrow(NavigationUnavailableException::class);

    expect(Cache::has('navigation.desktop_nav.v3'))->toBeFalse();
});

it('forgets cached navigation and reports the cleared keys', function () {
    setNavigationGlobal('desktop_nav', desktopNavFixture());
    setNavigationGlobal('mobile_nav', mobileNavFixture());

    $service = app(NavigationDataService::class);

    $service->all();

    expect($service->forgetCached())->toBe([
        'navigation.desktop_nav.v3',
        'navigation.mobile_nav.v3',
        'navigation.all.v3',
    ])
        ->and(Cache::has('navigation.all.v3'))->toBeFalse();
});
