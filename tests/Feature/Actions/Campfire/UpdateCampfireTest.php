<?php

use App\Actions\Event\UpdateEvent;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
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
