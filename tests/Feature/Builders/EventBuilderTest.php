<?php

use App\Models\Event;

beforeEach(function () {
    $this->seed('EventTypeSeeder');
});

it('gets events that were last approved more than 90 days ago', function () {
    Event::factory()->fires()->create([
        'name' => 'Event 1',
        'approved_at' => now()->subDays(91),
    ]);

    $eventQuery1 = Event::query()
        ->lastApprovedAt(90, '<')
        ->get();

    expect($eventQuery1->count())->toBe(1);

});

it('does not get events that were approved less than 90 days ago', function () {
    Event::factory()->fires()->create([
        'name' => 'Event 1',
        'approved_at' => now()->subDays(81),
    ]);

    $eventQuery1 = Event::query()
        ->lastApprovedAt(90, '<')
        ->get();

    expect($eventQuery1->count())->toBe(0);

});

it('gets events where the last monthly email was sent more than 7 days ago', function () {
    Event::factory()->fires()->create([
        'name' => 'Event 1',
        'last_monthly_email_sent_at' => now()->subDays(8),
    ]);

    $eventQuery1 = Event::query()
        ->lastMonthlyEmailSentAgo(7)
        ->get();

    expect($eventQuery1->count())->toBe(1);

});

it('gets retreats only', function () {
    Event::factory()->fires()->create();
    Event::factory()->basicRetreat()->create();
    Event::factory()->captivatingCoreRetreat()->create();
    Event::factory()->becomingAKingRetreat()->create();

    $retreats = Event::query()
        ->retreats()
        ->get();

    expect($retreats->count())->toBe(3);
});

it('gets events that are upcoming', function () {
    Event::factory()->basicRetreat()->create([
        'start_date' => now()->addDays(1),
    ]);

    $upcomingEvents = Event::query()
        ->upcoming()
        ->get();

    expect($upcomingEvents->count())->toBe(1);
});

it('does not get events with a start date of more than 1 year ago', function () {
    Event::factory()->basicRetreat()->create([
        'start_date' => now()->subYear()->subDay(),
    ]);

    $eventsInTheLastYear = Event::query()
        ->inTheLastYear()
        ->get();

    expect($eventsInTheLastYear->count())->toBe(0);
});

it('gets events with a start date in the last year', function () {
    Event::factory()->basicRetreat()->create([
        'start_date' => now()->subYear()->addDay(),
    ]);

    Event::factory()->basicRetreat()->create([
        'start_date' => now()->subYear()->subDay(),
    ]);

    $eventsInTheLastYear = Event::query()
        ->inTheLastYear()
        ->get();

    expect($eventsInTheLastYear->count())->toBe(1);
});

it('gets retreats that are upcoming', function () {
    Event::factory()->basicRetreat()->create([
        'start_date' => now()->addDay(),
    ]);

    Event::factory()->basicRetreat()->create([
        'start_date' => now()->subDay(),
    ]);

    $upcomingRetreats = Event::query()
        ->retreats()
        ->upcoming()
        ->get();

    expect($upcomingRetreats->count())->toBe(1);
});
