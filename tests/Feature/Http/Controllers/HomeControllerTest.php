<?php

use App\Models\EventType;
use Inertia\Testing\AssertableInertia as Assert;

test('home page event types are ordered by their model order column', function () {
    cache()->forget('allEventTypes.v2');

    $laterRetreat = EventType::factory()->create([
        'name' => 'Second Retreat',
        'slug' => 'second-retreat',
        'order' => 2000,
    ]);

    $earlierRetreat = EventType::factory()->create([
        'name' => 'First Retreat',
        'slug' => 'first-retreat',
        'order' => 1000,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('HomePage'));

    $allEventTypes = collect($response->viewData('page')['props']['allEventTypes']);
    $retreatIds = $allEventTypes
        ->filter(fn (array $eventType) => str_contains(mb_strtolower($eventType['name']), 'retreat')
            || str_contains(mb_strtolower($eventType['name']), 'school'))
        ->pluck('id')
        ->values()
        ->all();

    expect(array_search($earlierRetreat->id, $retreatIds, true))
        ->toBeLessThan(array_search($laterRetreat->id, $retreatIds, true));
});

test('home page fire types are the event types in the fire group', function () {
    cache()->forget('fireTypes.v2');

    $fireWithoutFireSlug = EventType::factory()->create([
        'name' => 'Campout',
        'slug' => 'campout',
        'group_type' => EventType::TYPE_FIRE,
    ]);

    $retreatWithFireSlug = EventType::factory()->create([
        'name' => 'Fireside Retreat',
        'slug' => 'fireside-retreat',
        'group_type' => EventType::TYPE_RETREAT,
    ]);

    $fireTypeIds = collect($this->get(route('home'))->viewData('page')['props']['fireTypes'])->pluck('id');

    expect($fireTypeIds)->toContain($fireWithoutFireSlug->id)
        ->not->toContain($retreatWithFireSlug->id);
});
