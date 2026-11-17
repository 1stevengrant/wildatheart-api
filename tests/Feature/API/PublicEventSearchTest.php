<?php

use App\Models\Event;
use Statamic\Facades\GlobalSet;
use App\Services\EventCacheService;
use App\Services\PublicEventSearchService;

const MONTANA_BOUNDS = [
    'sw_latitude' => 44.0,
    'sw_longitude' => -113.0,
    'ne_latitude' => 47.0,
    'ne_longitude' => -109.0,
];

const EUROPE_BOUNDS = [
    'sw_latitude' => 45.0,
    'sw_longitude' => 0.0,
    'ne_latitude' => 55.0,
    'ne_longitude' => 10.0,
];

function publicSearchEvent(array $attributes = []): Event
{
    return Event::factory()
        ->wahExperiences()
        ->approved()
        ->create(array_merge([
            'latitude' => 45.677,
            'longitude' => -111.0429,
            'is_private' => false,
        ], $attributes));
}

/**
 * @param  array<int, string>  $showInRegions
 */
function setFeaturedContent(bool $enabled, array $showInRegions = []): void
{
    $globalSet = GlobalSet::findByHandle('local_platform');

    $variables = $globalSet->in('default') ?? $globalSet->makeLocalization('default');

    $variables
        ->set('featured_content_enabled', $enabled)
        ->set('show_in_regions', $showInRegions)
        ->set('featured_content_url', 'https://example.com/featured')
        ->set('featured_content_text', 'Featured')
        ->save();
}

it('only returns published, active, public events from the shared query', function () {
    $visible = publicSearchEvent();

    publicSearchEvent(['is_private' => true]);
    publicSearchEvent(['approved_at' => null]);
    publicSearchEvent(['start_date' => now()->subMonth()]);

    $ids = app(PublicEventSearchService::class)
        ->query(sex: null, typeGroup: null, types: null, startDate: null, endDate: null)
        ->pluck('id');

    expect($ids->all())->toBe([$visible->id]);
});

it('applies the same eligibility rules on the legacy and v1 endpoints', function () {
    $visible = publicSearchEvent();

    publicSearchEvent(['is_private' => true]);
    publicSearchEvent(['approved_at' => null]);

    $legacy = $this->getJson(route('api.events.search.legacy', MONTANA_BOUNDS))->assertOk();
    $v1 = $this->getJson(route('v1.events.search', MONTANA_BOUNDS))->assertOk();

    expect(collect($legacy->json('events'))->pluck('id')->all())->toBe([$visible->id])
        ->and(collect($v1->json('events'))->pluck('id')->all())->toBe([$visible->id]);
});

it('keeps the legacy and v1 schemas distinct for the same query', function () {
    publicSearchEvent();

    $legacy = $this->getJson(route('api.events.search.legacy', MONTANA_BOUNDS))->assertOk();
    $v1 = $this->getJson(route('v1.events.search', MONTANA_BOUNDS))->assertOk();

    $legacyEvent = $legacy->json('events.0');
    $v1Event = $v1->json('events.0');

    expect(array_keys($legacyEvent))->not->toEqualCanonicalizing(array_keys($v1Event));

    $legacy->assertJsonStructure(['events', 'featuredContent']);
    $v1->assertJsonStructure(['events', 'featuredContent']);
});

it('namespaces search cache keys by endpoint', function () {
    $cacheService = app(EventCacheService::class);

    $legacyKey = $cacheService->legacySearchCacheKey(MONTANA_BOUNDS);
    $v1Key = $cacheService->v1SearchCacheKey(MONTANA_BOUNDS);

    expect($legacyKey)->not->toBe($v1Key)
        ->and($legacyKey)->toStartWith('events_search:legacy:')
        ->and($v1Key)->toStartWith('events_search:v1:');
});

it('omits featured content when it is disabled', function () {
    setFeaturedContent(enabled: false);

    $this->getJson(route('api.events.search.legacy', MONTANA_BOUNDS))
        ->assertJsonPath('featuredContent', null);

    $this->getJson(route('v1.events.search', MONTANA_BOUNDS))
        ->assertJsonPath('featuredContent', null);
});

it('shows featured content everywhere when no regions are configured', function () {
    setFeaturedContent(enabled: true);

    $this->getJson(route('api.events.search.legacy', EUROPE_BOUNDS))
        ->assertJsonPath('featuredContent.featured_content_text', 'Featured');

    $this->getJson(route('v1.events.search', EUROPE_BOUNDS))
        ->assertJsonPath('featuredContent.featured_content_text', 'Featured');
});

it('shows featured content only when the viewport overlaps a configured region', function () {
    setFeaturedContent(enabled: true, showInRegions: ['north_america']);

    $this->getJson(route('api.events.search.legacy', MONTANA_BOUNDS))
        ->assertJsonPath('featuredContent.featured_content_text', 'Featured');

    $this->getJson(route('v1.events.search', MONTANA_BOUNDS))
        ->assertJsonPath('featuredContent.featured_content_text', 'Featured');

    $this->getJson(route('api.events.search.legacy', EUROPE_BOUNDS))
        ->assertJsonPath('featuredContent', null);

    $this->getJson(route('v1.events.search', EUROPE_BOUNDS))
        ->assertJsonPath('featuredContent', null);
});
