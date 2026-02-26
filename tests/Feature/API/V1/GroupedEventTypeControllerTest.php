<?php

use App\Models\EventType;
use App\Models\EventTypeGroup;

beforeEach(function () {
    EventType::truncate();
    EventTypeGroup::truncate();
    cache()->forget('eventTypesGroupedByType');
});

afterEach(function () {
    EventType::truncate();
    EventTypeGroup::truncate();
});

it('returns event types grouped by type group', function () {
    $gathering = EventTypeGroup::factory()->create(['name' => 'Gathering', 'sort_order' => 1]);
    $retreat = EventTypeGroup::factory()->create(['name' => 'Retreat', 'sort_order' => 2]);
    $course = EventTypeGroup::factory()->create(['name' => 'Course', 'sort_order' => 3]);

    EventType::factory()->create(['name' => 'Fires', 'group_type' => 'fire', 'type_id' => $gathering->id]);
    EventType::factory()->create(['name' => 'WAH Retreat', 'group_type' => 'retreat', 'type_id' => $retreat->id]);
    EventType::factory()->create(['name' => 'WAH Experience', 'group_type' => 'experience', 'type_id' => $course->id]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonCount(3)
        ->assertJsonPath('0.name', 'Gathering')
        ->assertJsonCount(1, '0.event_types')
        ->assertJsonPath('1.name', 'Retreat')
        ->assertJsonCount(1, '1.event_types')
        ->assertJsonPath('2.name', 'Course')
        ->assertJsonCount(1, '2.event_types');
});

it('groups multiple event types under the same type group', function () {
    $gathering = EventTypeGroup::factory()->create(['name' => 'Gathering', 'sort_order' => 1]);
    $retreat = EventTypeGroup::factory()->create(['name' => 'Retreat', 'sort_order' => 2]);

    EventType::factory()->create(['name' => 'Fires', 'group_type' => 'fire', 'type_id' => $gathering->id]);
    EventType::factory()->create(['name' => 'Captivating Fires', 'group_type' => 'fire', 'type_id' => $gathering->id]);
    EventType::factory()->create(['name' => 'WAH Retreat', 'group_type' => 'retreat', 'type_id' => $retreat->id]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonCount(2, '0.event_types')
        ->assertJsonCount(1, '1.event_types');
});

it('returns group metadata with event types', function () {
    $group = EventTypeGroup::factory()->create([
        'name' => 'Gathering',
        'icon' => '<svg>icon</svg>',
        'width' => 24,
        'height' => 24,
        'summary' => 'A gathering summary',
        'sort_order' => 1,
    ]);

    EventType::factory()->create([
        'name' => 'Fires',
        'group_type' => 'fire',
        'type_id' => $group->id,
        'sex' => 'men',
        'summary' => 'A fire summary',
        'summary_link' => 'https://example.com',
    ]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonPath('0.name', 'Gathering')
        ->assertJsonPath('0.icon', '<svg>icon</svg>')
        ->assertJsonPath('0.width', 24)
        ->assertJsonPath('0.height', 24)
        ->assertJsonPath('0.summary', 'A gathering summary')
        ->assertJsonPath('0.event_types.0.name', 'Fires');
});

it('returns empty json when no event type groups exist', function () {
    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJson([]);
});

it('respects the order column within groups', function () {
    $gathering = EventTypeGroup::factory()->create(['name' => 'Gathering', 'sort_order' => 1]);

    EventType::factory()->create(['name' => 'Second', 'group_type' => 'fire', 'type_id' => $gathering->id, 'order' => 2]);
    EventType::factory()->create(['name' => 'First', 'group_type' => 'fire', 'type_id' => $gathering->id, 'order' => 1]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful();

    $items = $response->json('0.event_types');
    expect($items[0]['name'])->toBe('First')
        ->and($items[1]['name'])->toBe('Second');
});

it('respects the sort_order of groups', function () {
    $second = EventTypeGroup::factory()->create(['name' => 'Retreat', 'sort_order' => 2]);
    $first = EventTypeGroup::factory()->create(['name' => 'Gathering', 'sort_order' => 1]);

    EventType::factory()->create(['type_id' => $second->id]);
    EventType::factory()->create(['type_id' => $first->id]);

    $response = $this->getJson('/api/v1/events/types/grouped');

    $response->assertSuccessful()
        ->assertJsonPath('0.name', 'Gathering')
        ->assertJsonPath('1.name', 'Retreat');
});
