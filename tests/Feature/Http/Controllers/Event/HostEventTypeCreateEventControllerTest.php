<?php

use App\Models\EventType;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->user = \App\Models\User::factory()->create();
    $this->startDate = now()->addDays(10)->format('Y-m-d');
});

it('creates a new event of a given type', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::WAH_RETREAT))->first();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
        'number_of_items_in_material_order' => 10,
    ]);

    expect($this->user->events()->count())->toBe(1);
});

test('an event that has no materials order does not request the number of items in material order to be present', function () {
    $this->actingAs($this->user);
    $eventType = EventType::query()->slug(\Illuminate\Support\Str::slug(EventType::BECOMING_A_KING))->first();
    $eventType->allow_materials_order = false;
    $eventType->save();
    $this->post(route('host.eventType.create.store', $eventType), [
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'US',
        'latitude' => 45.1234,
        'longitude' => -110.1234,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'MT',
        'start_date' => $this->startDate,
    ]);

    expect($this->user->events()->count())->toBe(1);
});
