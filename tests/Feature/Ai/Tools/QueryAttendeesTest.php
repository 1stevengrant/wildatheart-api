<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\EventType;
use Laravel\Ai\Tools\Request;
use App\Ai\Tools\QueryAttendees;

beforeEach(function () {
    Event::unsetEventDispatcher();
    User::unsetEventDispatcher();
    Attendee::unsetEventDispatcher();
});

function queryAttendees(array $arguments): array
{
    return json_decode(app(QueryAttendees::class)->handle(new Request($arguments)), true);
}

it('groups signups by event type within a date range', function () {
    $fire = Event::factory()->fires()->create(['country' => 'zz-fires']);
    $retreat = Event::factory()->wahRetreat()->create(['country' => 'zz-fires']);

    Attendee::factory()->count(2)->create(['event_id' => $fire->id, 'created_at' => '2026-02-10']);
    Attendee::factory()->create(['event_id' => $retreat->id, 'created_at' => '2026-02-11']);
    Attendee::factory()->create(['event_id' => $fire->id, 'created_at' => '2025-12-01']);

    $result = queryAttendees([
        'mode' => 'group_by',
        'group_by' => 'event_type',
        'event_country' => 'zz-fires',
        'signed_up_from' => '2026-01-01',
        'signed_up_to' => '2026-12-31',
    ]);

    expect($result['groups'])->toBe([
        ['bucket' => EventType::FIRES, 'count' => 2],
        ['bucket' => EventType::WAH_RETREAT, 'count' => 1],
    ]);
});

it('lists attendees with contact details for an event', function () {
    $event = Event::factory()->fires()->create(['name' => 'Hilltop Fire']);

    Attendee::factory()->create([
        'event_id' => $event->id,
        'given_name' => 'Grace',
        'family_name' => 'Hopper',
        'email' => 'grace@example.com',
    ]);

    $result = queryAttendees(['mode' => 'list', 'event_id' => $event->id]);

    expect($result['total'])->toBe(1)
        ->and($result['attendees'][0])->toMatchArray([
            'name' => 'Grace Hopper',
            'email' => 'grace@example.com',
            'event' => ['id' => $event->id, 'name' => 'Hilltop Fire'],
        ]);
});

it('excludes attendees of deleted events', function () {
    $event = Event::factory()->fires()->create();

    Attendee::factory()->create(['event_id' => $event->id]);

    $event->delete();

    expect(queryAttendees(['mode' => 'count', 'event_id' => $event->id]))->toBe(['count' => 0]);
});
