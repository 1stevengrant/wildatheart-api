<?php

use Illuminate\Support\Facades\Route;

it('registers the api.events.search.legacy route the map frontend depends on', function () {
    // The HomePage map (resources/js/Hooks/useEvents.ts) resolves this route
    // name via Ziggy's route() helper. If the name changes, route() throws and
    // no events load on the main page.
    expect(Route::has('api.events.search.legacy'))->toBeTrue();
});

it('responds successfully to the events search endpoint', function () {
    $response = $this->getJson(route('api.events.search.legacy', [
        'sw_latitude' => -90,
        'sw_longitude' => -180,
        'ne_latitude' => 90,
        'ne_longitude' => 180,
    ]));

    $response->assertSuccessful()
        ->assertJsonStructure(['events', 'featuredContent']);
});
