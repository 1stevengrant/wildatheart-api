<?php

use App\Models\EventType;
use App\Models\EventTypeGroup;

beforeEach(function () {
    EventType::truncate();
    EventTypeGroup::truncate();
});

afterEach(function () {
    EventType::truncate();
    EventTypeGroup::truncate();
});

it('gets a list of active event types', function () {
    EventType::factory()->create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => true,
    ]);

    $eventTypes = EventType::active()->get();

    expect($eventTypes->count())->toBe(1);
});

it('gets a list of inactive event types', function () {
    EventType::factory()->create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => false,
    ]);

    EventType::factory()->create([
        'name' => 'Wild at Heart Experience',
        'is_active' => true,
    ]);

    $eventTypes = EventType::inactive()->get();

    expect($eventTypes->count())->toBe(1);
});

it('clears the cache when a new event type is created', function () {
    cache()->put('allEventTypes', 'cached', now()->addDay());

    EventType::factory()->create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => true,
    ]);

    expect(cache()->has('allEventTypes'))->toBeFalse();
});

it('can create an event type with summary and summary_link', function () {
    $eventType = EventType::factory()->create([
        'summary' => 'This is a summary of the event type',
        'summary_link' => 'https://example.com/summary',
    ]);

    $eventType->refresh();

    expect($eventType->summary)->toBe('This is a summary of the event type')
        ->and($eventType->summary_link)->toBe('https://example.com/summary');
});

it('can create an event type with nullable summary and summary_link', function () {
    $eventType = EventType::factory()->create([
        'summary' => null,
        'summary_link' => null,
    ]);

    expect($eventType->summary)->toBeNull()
        ->and($eventType->summary_link)->toBeNull();
});

it('includes summary and summary_link in EventTypeData', function () {
    $eventType = EventType::factory()->create([
        'summary' => 'Test summary',
        'summary_link' => 'https://example.com',
    ]);

    $data = \App\Data\EventTypeData::fromModel($eventType);

    expect($data->summary)->toBe('Test summary')
        ->and($data->summary_link)->toBe('https://example.com');
});

it('includes type group name in V1 EventTypeData', function () {
    $group = EventTypeGroup::factory()->create(['name' => 'Retreat']);

    $eventType = EventType::factory()->create([
        'type_id' => $group->id,
    ]);

    $data = \App\Data\V1\EventTypeData::fromEventType($eventType);

    expect($data->type_group)->toBe('Retreat');
});

it('belongs to an event type group', function () {
    $group = EventTypeGroup::factory()->create(['name' => 'Gathering']);

    $eventType = EventType::factory()->create([
        'type_id' => $group->id,
    ]);

    expect($eventType->type)->toBeInstanceOf(EventTypeGroup::class)
        ->and($eventType->type->name)->toBe('Gathering');
});
