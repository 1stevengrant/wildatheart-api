<?php

use App\Models\Event;
use App\Enums\EventSexEnum;
use App\Data\V1\SearchEventsData;

/**
 * Bozeman, Montana. Every fixture below is positioned relative to it.
 */
const SEARCH_ORIGIN_LAT = 45.6770;

const SEARCH_ORIGIN_LNG = -111.0429;

function eventAt(float $latitude, float $longitude, array $attributes = []): Event
{
    return Event::factory()
        ->wahExperiences()
        ->approved()
        ->create(array_merge([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'is_private' => false,
        ], $attributes));
}

it('returns the nearest events regardless of distance, ordered by distance', function () {
    $closest = eventAt(SEARCH_ORIGIN_LAT + 0.1, SEARCH_ORIGIN_LNG);
    $middle = eventAt(SEARCH_ORIGIN_LAT + 5, SEARCH_ORIGIN_LNG);
    $furthest = eventAt(SEARCH_ORIGIN_LAT + 20, SEARCH_ORIGIN_LNG);

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 2,
    ]));

    $response->assertSuccessful();

    $ids = collect($response->json('events'))->pluck('id')->all();

    expect($ids)->toBe([$closest->id, $middle->id])
        ->and($ids)->not->toContain($furthest->id);
});

it('returns the effective radius needed to reach the nearest events', function () {
    eventAt(SEARCH_ORIGIN_LAT + 0.1, SEARCH_ORIGIN_LNG);
    eventAt(SEARCH_ORIGIN_LAT + 5, SEARCH_ORIGIN_LNG);

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 2,
    ]));

    $furthestDistance = collect($response->json('events'))->max('distance');

    expect($response->json('radius'))->toBeGreaterThanOrEqual($furthestDistance)
        ->and($response->json('total_count'))->toBe(2)
        ->and($response->json('pagination'))->toBeNull();
});

it('returns every event and a null radius when fewer than N events exist', function () {
    eventAt(SEARCH_ORIGIN_LAT + 0.1, SEARCH_ORIGIN_LNG);

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 20,
    ]));

    expect($response->json('events'))->toHaveCount(1);

    $empty = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT + 40,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 20,
        'sex' => EventSexEnum::WOMEN->value,
    ]));

    expect($empty->json('events'))->toBeEmpty()
        ->and($empty->json('radius'))->toBeNull()
        ->and($empty->json('total_count'))->toBe(0);
});

it('applies the sex filter in nearest mode', function () {
    eventAt(SEARCH_ORIGIN_LAT + 0.1, SEARCH_ORIGIN_LNG, ['sex' => EventSexEnum::MEN]);
    $women = eventAt(SEARCH_ORIGIN_LAT + 5, SEARCH_ORIGIN_LNG, ['sex' => EventSexEnum::WOMEN]);

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 20,
        'sex' => EventSexEnum::WOMEN->value,
    ]));

    expect(collect($response->json('events'))->pluck('id')->all())->toBe([$women->id]);
});

it('applies the event type filter in nearest mode', function () {
    $experience = eventAt(SEARCH_ORIGIN_LAT + 0.1, SEARCH_ORIGIN_LNG);
    eventAt(SEARCH_ORIGIN_LAT + 0.2, SEARCH_ORIGIN_LNG);

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 20,
        'types' => (string) $experience->event_type_id,
    ]));

    expect($response->json('events'))->toHaveCount(2);

    $none = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 20,
        'types' => (string) ($experience->event_type_id + 9999),
    ]));

    expect($none->json('events'))->toBeEmpty();
});

it('rejects a nearest search without a center point', function () {
    $this->getJson(route('v1.events.search', ['nearest' => 10]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['latitude', 'longitude']);
});

it('rejects a nearest value above the maximum', function () {
    $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => SearchEventsData::MAX_NEAREST + 1,
    ]))->assertStatus(422)->assertJsonValidationErrors(['nearest']);
});

it('paginates the center point search when per_page is supplied', function () {
    foreach (range(1, 5) as $offset) {
        eventAt(SEARCH_ORIGIN_LAT + ($offset / 100), SEARCH_ORIGIN_LNG);
    }

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'radius' => 500,
        'per_page' => 2,
    ]));

    $response->assertSuccessful();

    expect($response->json('events'))->toHaveCount(2)
        ->and($response->json('total_count'))->toBe(5)
        ->and($response->json('pagination'))->toBe([
            'current_page' => 1,
            'per_page' => 2,
            'total' => 5,
            'last_page' => 3,
            'has_more' => true,
        ]);

    $lastPage = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'radius' => 500,
        'per_page' => 2,
        'page' => 3,
    ]));

    expect($lastPage->json('events'))->toHaveCount(1)
        ->and($lastPage->json('pagination.has_more'))->toBeFalse();
});

it('composes pagination with the min_radius ring search', function () {
    eventAt(SEARCH_ORIGIN_LAT + 0.01, SEARCH_ORIGIN_LNG);
    $outer = collect(range(1, 3))
        ->map(fn (int $offset) => eventAt(SEARCH_ORIGIN_LAT + 3 + ($offset / 100), SEARCH_ORIGIN_LNG));

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'min_radius' => 100,
        'radius' => 500,
        'per_page' => 2,
    ]));

    $response->assertSuccessful();

    expect($response->json('events'))->toHaveCount(2)
        ->and($response->json('total_count'))->toBe($outer->count())
        ->and($response->json('pagination.last_page'))->toBe(2);
});

it('leaves the unpaginated center point response shape untouched', function () {
    eventAt(SEARCH_ORIGIN_LAT + 0.01, SEARCH_ORIGIN_LNG);

    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'radius' => 500,
    ]));

    $response->assertSuccessful()
        ->assertJsonStructure(['events', 'radius', 'total_count', 'pagination', 'featuredContent']);

    expect($response->json('pagination'))->toBeNull()
        ->and($response->json('radius'))->toBe(500);
});

it('returns 422 rather than 302 for a parameterless search', function () {
    $this->get(route('v1.events.search'))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['sw_latitude', 'sw_longitude', 'ne_latitude', 'ne_longitude']);
});

it('allows shared caches to reuse search responses', function () {
    $response = $this->getJson(route('v1.events.search', [
        'latitude' => SEARCH_ORIGIN_LAT,
        'longitude' => SEARCH_ORIGIN_LNG,
        'nearest' => 5,
    ]));

    $cacheControl = $response->headers->get('cache-control');

    expect($cacheControl)->toContain('public')
        ->and($cacheControl)->toContain('max-age=60')
        ->and($cacheControl)->toContain('s-maxage=60')
        ->and($cacheControl)->not->toContain('no-cache')
        ->and($cacheControl)->not->toContain('private');
});

it('treats a zero latitude or longitude as a real center point', function () {
    eventAt(0.1, 0.1);

    $this->getJson(route('v1.events.search', [
        'latitude' => 0,
        'longitude' => 0,
        'nearest' => 5,
    ]))->assertSuccessful()->assertJsonCount(1, 'events');
});
