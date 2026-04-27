<?php

use App\Models\EventType;
use App\Enums\EventTypeEnum;

beforeEach(function () {
    EventType::truncate();
    cache()->forget('eventTypesGroupedByType');
});

afterEach(function () {
    EventType::truncate();
});

it('returns event types grouped by type', function () {
    EventType::factory()->create(['name' => 'Fires', 'group_type' => 'fire', 'type' => EventTypeEnum::Gathering]);
    EventType::factory()->create(['name' => 'WAH Retreat', 'group_type' => 'retreat', 'type' => EventTypeEnum::Retreat]);
    EventType::factory()->create(['name' => 'WAH Experience', 'group_type' => 'experience', 'type' => EventTypeEnum::Course]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'gathering')
        ->assertJsonCount(1, 'retreat')
        ->assertJsonCount(1, 'course');
});

it('groups multiple event types under the same type', function () {
    EventType::factory()->create(['name' => 'Fires', 'group_type' => 'fire', 'type' => EventTypeEnum::Gathering]);
    EventType::factory()->create(['name' => 'Captivating Fires', 'group_type' => 'fire', 'type' => EventTypeEnum::Gathering]);
    EventType::factory()->create(['name' => 'WAH Retreat', 'group_type' => 'retreat', 'type' => EventTypeEnum::Retreat]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'gathering')
        ->assertJsonCount(1, 'retreat');
});

it('excludes event types with null type', function () {
    EventType::factory()->create(['name' => 'Fires', 'group_type' => 'fire', 'type' => EventTypeEnum::Gathering]);
    EventType::factory()->create(['name' => 'Legacy Type', 'group_type' => 'fire', 'type' => null]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'gathering')
        ->assertJsonMissing(['name' => 'Legacy Type']);
});

it('returns event type data with expected fields', function () {
    EventType::factory()->create([
        'name' => 'Fires',
        'group_type' => 'fire',
        'type' => EventTypeEnum::Gathering,
        'sex' => 'men',
        'summary' => 'A fire summary',
        'summary_link' => 'https://example.com',
    ]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonFragment([
            'name' => 'Fires',
            'group_type' => 'fire',
            'type' => 'gathering',
            'summary' => 'A fire summary',
            'summary_link' => 'https://example.com',
        ]);
});

it('returns empty json when no event types exist', function () {
    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJson([]);
});

it('respects the order column', function () {
    EventType::factory()->create(['name' => 'Second', 'group_type' => 'fire', 'type' => EventTypeEnum::Gathering, 'order' => 2]);
    EventType::factory()->create(['name' => 'First', 'group_type' => 'fire', 'type' => EventTypeEnum::Gathering, 'order' => 1]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful();

    $gathering = $response->json('gathering');
    expect($gathering[0]['name'])->toBe('First')
        ->and($gathering[1]['name'])->toBe('Second');
});
