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
