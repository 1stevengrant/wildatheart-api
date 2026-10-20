<?php

use App\Models\User;
use App\Models\Event;
use App\Ai\Tools\QueryHosts;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    Event::unsetEventDispatcher();
    User::unsetEventDispatcher();
});

function queryHosts(array $arguments): array
{
    return json_decode(app(QueryHosts::class)->handle(new Request($arguments)), true);
}

it('finds fire hosts without upcoming events', function () {
    $activeHost = User::factory()->create();
    $quietHost = User::factory()->create(['email' => 'quiet@example.com']);
    $retreatHost = User::factory()->create();

    Event::factory()->fires()->create(['user_id' => $activeHost->id, 'start_date' => now()->addWeek()]);
    Event::factory()->fires()->create(['user_id' => $quietHost->id, 'start_date' => now()->subMonth()]);
    Event::factory()->wahRetreat()->create(['user_id' => $retreatHost->id, 'start_date' => now()->subMonth()]);

    $result = queryHosts([
        'mode' => 'list',
        'event_types' => ['fires'],
        'has_upcoming_events' => false,
    ]);

    expect(collect($result['hosts'])->pluck('email')->all())->toBe(['quiet@example.com'])
        ->and($result['hosts'][0])->toMatchArray(['events_count' => 1, 'upcoming_events_count' => 0]);
});

it('counts hosts with events in a country', function () {
    Event::factory()->fires()->create(['country' => 'gb']);
    Event::factory()->fires()->create(['country' => 'United Kingdom']);
    Event::factory()->fires()->create(['country' => 'us']);

    expect(queryHosts(['mode' => 'count', 'event_country' => 'UK']))->toBe(['count' => 2]);
});

it('ignores users without events', function () {
    User::factory()->create();

    expect(queryHosts(['mode' => 'count']))->toBe(['count' => User::query()->hasEvents()->count()]);
});
