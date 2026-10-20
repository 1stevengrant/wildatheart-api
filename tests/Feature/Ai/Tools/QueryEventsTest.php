<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Ai\Tools\QueryEvents;
use Laravel\Ai\Tools\Request;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Event::unsetEventDispatcher();
    User::unsetEventDispatcher();
});

function queryEvents(array $arguments): array
{
    return json_decode(app(QueryEvents::class)->handle(new Request($arguments)), true);
}

it('counts events matching the filters', function () {
    Event::factory()->fires()->approved()->create(['country' => 'us', 'state' => 'TX']);
    Event::factory()->fires()->approved()->create(['country' => 'United States', 'state' => 'Texas']);
    Event::factory()->fires()->create(['country' => 'United States', 'state' => 'Texas']);
    Event::factory()->fires()->approved()->create(['country' => 'Australia', 'state' => 'Queensland']);
    Event::factory()->wahRetreat()->approved()->create(['country' => 'us', 'state' => 'TX']);

    $result = queryEvents([
        'mode' => 'count',
        'event_types' => ['fires'],
        'country' => 'United States',
        'state' => 'Texas',
        'statuses' => ['published'],
    ]);

    expect($result)->toBe(['count' => 2]);
});

it('does not match countries by substring', function () {
    Event::factory()->fires()->create(['country' => 'us']);
    Event::factory()->fires()->create(['country' => 'Australia']);
    Event::factory()->fires()->create(['country' => 'Russia']);

    expect(queryEvents(['mode' => 'count', 'country' => 'us']))->toBe(['count' => 1]);
});

it('lists events with their host', function () {
    $host = User::factory()->create(['given_name' => 'Ada', 'family_name' => 'Lovelace', 'email' => 'ada@example.com']);

    Event::factory()->fires()->create(['user_id' => $host->id, 'name' => 'Riverside Fire']);

    $result = queryEvents(['mode' => 'list', 'host_id' => $host->id]);

    expect($result['total'])->toBe(1)
        ->and($result['events'][0])
        ->name->toBe('Riverside Fire')
        ->event_type->toBe(EventType::FIRES)
        ->host->toMatchArray(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
});

it('groups events by event type', function () {
    $host = User::factory()->create();

    Event::factory()->fires()->count(2)->create(['user_id' => $host->id]);
    Event::factory()->wahRetreat()->create(['user_id' => $host->id]);

    $result = queryEvents(['mode' => 'group_by', 'group_by' => 'event_type', 'host_id' => $host->id]);

    expect($result['total'])->toBe(3)
        ->and($result['groups'])->toBe([
            ['bucket' => EventType::FIRES, 'count' => 2],
            ['bucket' => EventType::WAH_RETREAT, 'count' => 1],
        ]);
});

it('filters by start date range', function () {
    $host = User::factory()->create();

    Event::factory()->fires()->create(['user_id' => $host->id, 'start_date' => '2026-03-15 10:00:00']);
    Event::factory()->fires()->create(['user_id' => $host->id, 'start_date' => '2026-04-01 10:00:00']);

    $result = queryEvents([
        'mode' => 'count',
        'host_id' => $host->id,
        'start_date_from' => '2026-03-01',
        'start_date_to' => '2026-03-31',
    ]);

    expect($result)->toBe(['count' => 1]);
});

it('excludes soft deleted events unless asked', function () {
    $host = User::factory()->create();

    Event::factory()->fires()->create(['user_id' => $host->id]);
    Event::factory()->fires()->create(['user_id' => $host->id])->delete();

    expect(queryEvents(['mode' => 'count', 'host_id' => $host->id]))->toBe(['count' => 1])
        ->and(queryEvents(['mode' => 'count', 'host_id' => $host->id, 'include_deleted' => true]))->toBe(['count' => 2]);
});

it('returns the valid event types when none match', function () {
    $result = queryEvents(['mode' => 'count', 'event_types' => ['bonfires']]);

    expect($result['error'])->toBe('None of the given event types exist.')
        ->and($result['event_types'])->toContain(EventType::FIRES);
});

it('requires a group when grouping', function () {
    queryEvents(['mode' => 'group_by']);
})->throws(ValidationException::class);
