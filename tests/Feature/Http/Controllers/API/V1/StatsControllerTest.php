<?php

use App\Models\Event;

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
});

it('returns the stats', function () {
    $this->getJson(route('v1.stats'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'retreats' => [
                    '*' => [
                        'label',
                        'count',
                    ],
                ],
                'experiences' => [
                    '*' => [
                        'label',
                        'count',
                    ],
                ],
                'fires' => [
                    '*' => [
                        'label',
                        'count',
                    ],
                ],
            ],
        ]);
});

it('caches the stats', function () {
    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->assertTrue(cache()->has('event_stats'));
});

it('caches the stats for an hour', function () {
    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->travel(61)->minutes();

    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->assertTrue(cache()->has('event_stats'));
});

it('returns the number of upcoming retreats', function () {
    Event::factory()->fires()->create();
    Event::factory()->wahRetreat()->create();
    Event::factory()->captivatingCoreRetreat()->create();
    Event::factory()->becomingAKingRetreat()->create();

    $this->getJson(route('v1.stats'))
        ->assertOk()
        ->assertJsonFragment([
            'label' => 'Current Retreat Listings',
            'count' => 3,
        ]);
});
