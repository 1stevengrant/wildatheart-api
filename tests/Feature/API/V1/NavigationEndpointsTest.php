<?php

use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\Cache;
use App\Services\NavigationDataService;

function saveNavigationGlobal(string $handle, array $data): void
{
    $globalSet = GlobalSet::findByHandle($handle);

    $variables = $globalSet->in('default') ?? $globalSet->makeLocalization('default');

    $variables->data($data)->save();
}

beforeEach(function () {
    Cache::flush();
});

it('returns desktop navigation with cache and type headers', function () {
    saveNavigationGlobal('desktop_nav', [
        'desktop_nav' => [
            ['id' => 'about', 'title' => 'About', 'url' => '/about', 'enabled' => true],
            ['id' => 'off', 'title' => 'Off', 'enabled' => false],
        ],
    ]);

    $this->getJson('/api/v1/navs/desktop')
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=3600, public')
        ->assertHeader('X-Navigation-Type', 'desktop_nav')
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'about')
        ->assertJsonPath('0.title', 'About');
});

it('returns mobile navigation with cache and type headers', function () {
    saveNavigationGlobal('mobile_nav', [
        'main_nav_bar' => [
            ['id' => 'home', 'title' => 'Home', 'url' => 'https://example.com', 'enabled' => true],
        ],
        'drawer_items' => [],
    ]);

    $this->getJson('/api/v1/navs/mobile')
        ->assertOk()
        ->assertHeader('X-Navigation-Type', 'mobile_nav')
        ->assertJsonPath('main_nav_bar.0.id', 'home')
        ->assertJsonPath('drawer_items', []);
});

it('returns all navigation with empty sections for unavailable navigation', function () {
    $this->getJson('/api/v1/navs/all')
        ->assertOk()
        ->assertHeader('X-Navigation-Type', 'all')
        ->assertExactJson([
            'desktop' => [],
            'mobile' => [],
        ]);
});

it('returns the not found error schema when navigation is unavailable', function () {
    $this->getJson('/api/v1/navs/mobile')
        ->assertNotFound()
        ->assertExactJson([
            'error' => 'Navigation data is empty',
            'code' => 404,
            'navigation_type' => 'mobile_nav',
        ]);
});

it('returns the internal error schema when navigation fails unexpectedly', function () {
    $this->mock(NavigationDataService::class)
        ->shouldReceive('desktop')
        ->andThrow(new RuntimeException('Boom'));

    $this->getJson('/api/v1/navs/desktop')
        ->assertStatus(500)
        ->assertExactJson([
            'error' => 'Internal server error',
            'code' => 500,
            'navigation_type' => 'desktop_nav',
        ]);
});
