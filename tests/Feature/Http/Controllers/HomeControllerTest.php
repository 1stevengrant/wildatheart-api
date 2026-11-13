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

test('home page only sends the event type props the map reads', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('HomePage')
            ->has('allEventTypes')
            ->has('fireTypes')
            ->missing('experienceTypes')
            ->missing('retreatTypes'));
});
