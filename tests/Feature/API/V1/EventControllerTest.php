<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Bus::fake();
    Mail::fake();
});

function makeEventWithoutType(): Event
{
    return Event::factory()->create([
        'user_id' => User::factory()->create()->id,
        'event_type_id' => null,
    ]);
}

it('returns 404 with a clear message when the event has no event type', function () {
    $event = makeEventWithoutType();

    $response = $this->getJson(route('v1.events.events.show', $event));

    $response->assertNotFound()
        ->assertSee('Event is missing an event type and cannot be served.');
});

it('excludes events with a null event type from the index endpoint', function () {
    cache()->forget('events_collection.v2');

    makeEventWithoutType();

    $response = $this->getJson(route('v1.events.events.index'));

    $response->assertSuccessful();
});
