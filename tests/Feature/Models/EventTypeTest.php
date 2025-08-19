<?php

use App\Models\EventType;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    EventType::truncate();
});

afterEach(function () {
    EventType::truncate();
});

it('gets a list of active event types', function () {
    EventType::create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => true,
    ]);

    $eventTypes = EventType::active()->get();

    expect($eventTypes->count())->toBe(1);
});

it('gets a list of inactive event types', function () {
    EventType::create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => false,
    ]);

    EventType::create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => true,
    ]);

    $eventTypes = EventType::inactive()->get();

    expect($eventTypes->count())->toBe(1);
});

it('clears the cache when a new event type is created', function () {
    cache()->put('allEventTypes', 'cached', now()->addDay());

    EventType::create([
        'name' => 'Wild at Heart Retreat',
        'is_active' => true,
    ]);

    expect(cache()->has('allEventTypes'))->toBeFalse();
});
