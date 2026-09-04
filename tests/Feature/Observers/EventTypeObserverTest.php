<?php

use App\Models\EventType;

$cacheKeys = [
    'allEventTypes.v2',
    'experienceTypes.v2',
    'retreatTypes.v2',
    'fireTypes.v2',
    'eventTypesGroupedByType.v2',
    'event_types.v2',
    'event_types_list.v2',
];

beforeEach(function () use ($cacheKeys) {
    foreach ($cacheKeys as $key) {
        cache()->forget($key);
    }
});

it('clears event type caches when an event type is created', function () use ($cacheKeys) {
    foreach ($cacheKeys as $key) {
        cache()->put($key, 'cached', now()->addDay());
    }

    EventType::factory()->create();

    foreach ($cacheKeys as $key) {
        expect(cache()->has($key))->toBeFalse("Cache key {$key} should be cleared");
    }
});

it('clears event type caches when an event type is updated', function () use ($cacheKeys) {
    $eventType = EventType::factory()->create();

    foreach ($cacheKeys as $key) {
        cache()->put($key, 'cached', now()->addDay());
    }

    $eventType->update(['name' => 'Updated Name']);

    foreach ($cacheKeys as $key) {
        expect(cache()->has($key))->toBeFalse("Cache key {$key} should be cleared");
    }
});

it('clears event type caches when an event type is deleted', function () use ($cacheKeys) {
    $eventType = EventType::factory()->create();

    foreach ($cacheKeys as $key) {
        cache()->put($key, 'cached', now()->addDay());
    }

    $eventType->delete();

    foreach ($cacheKeys as $key) {
        expect(cache()->has($key))->toBeFalse("Cache key {$key} should be cleared");
    }
});
