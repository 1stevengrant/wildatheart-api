<?php

use App\Models\Event;
use App\Actions\Event\UpdateEvent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('updates an event with given data', function () {
    $event = Event::factory()->fires()->create();

    $newData = ['name' => 'Steven'];

    UpdateEvent::run(data: $newData, event: $event);

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => 'Steven',
    ]);
});
