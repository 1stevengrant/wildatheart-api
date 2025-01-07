<?php

use App\Models\Event;
use App\Actions\Event\UpdateEvent;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

it('updates an event with given data', function () {
    $event = Event::factory()->fires()->create();

    $newData = ['name' => 'Steven'];

    UpdateEvent::run(event: $event, data: $newData);

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => 'Steven',
    ]);
});
