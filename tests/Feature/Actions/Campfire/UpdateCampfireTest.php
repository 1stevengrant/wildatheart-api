<?php

use App\Actions\Event\UpdateEvent;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates an event with given data', function () {
    $event = Event::factory()->create();

    $newData = ['name' => 'Steven'];

    UpdateEvent::run(event: $event, data: $newData);

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => 'Steven',
    ]);
});
