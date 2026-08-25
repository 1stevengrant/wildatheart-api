<?php

use App\Models\Event as EventModel;
use App\Models\EventType as EventTypeModel;

beforeEach(function () {
    EventModel::unsetEventDispatcher();
    cache()->flush();
});

it('returns the requested number of nearest matching events without a radius limit', function () {
    $retreatType = EventTypeModel::factory()->create([
        'group_type' => EventTypeModel::TYPE_RETREAT,
        'new_image' => 'retreat.jpg',
    ]);

    $nearestRetreat = EventModel::factory()->approved()->for($retreatType)->create([
        'name' => 'Nearest retreat',
        'latitude' => -36.11,
        'longitude' => 174.59,
        'start_date' => now()->addMonth(),
        'is_private' => false,
    ]);

    $moreDistantRetreat = EventModel::factory()->approved()->for($retreatType)->create([
        'name' => 'More distant retreat',
        'latitude' => -41.2865,
        'longitude' => 174.7762,
        'start_date' => now()->addMonth(),
        'is_private' => false,
    ]);

    $retreatBeyondLimit = EventModel::factory()->approved()->for($retreatType)->create([
        'name' => 'Retreat beyond the limit',
        'latitude' => -45.0312,
        'longitude' => 168.6626,
        'start_date' => now()->addMonth(),
        'is_private' => false,
    ]);

    $response = $this->getJson('/api/v1/events/search?latitude=-37.6878&longitude=176.1651&type_group=retreat&nearest=2');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'events')
        ->assertJsonPath('events.0.id', $nearestRetreat->id)
        ->assertJsonPath('events.1.id', $moreDistantRetreat->id)
        ->assertJsonPath('total_count', 2)
        ->assertJsonMissing(['id' => $retreatBeyondLimit->id]);
});

it('validates the nearest result limit', function () {
    $this->getJson('/api/v1/events/search?latitude=-37.6878&longitude=176.1651&nearest=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('nearest');
});
