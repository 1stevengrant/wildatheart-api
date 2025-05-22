<?php

use App\Models\User;
use App\Models\Event;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
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
        'last_checkin_email_sent_at' => now()->subDays(8),
    ]);

    $eventQuery1 = Event::query()
        ->lastMonthlyEmailSentAgo(7)
        ->get();

    expect($eventQuery1->count())->toBe(1);
});

it('gets retreats only', function () {
    Event::factory()->fires()->create();
    Event::factory()->wahRetreat()->create();
    Event::factory()->captivatingCoreRetreat()->create();
    Event::factory()->becomingAKingRetreat()->create();

    $retreats = Event::query()
        ->retreats()
        ->get();

    expect($retreats->count())->toBe(3);
});

it('gets events that are upcoming', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->addDays(1),
    ]);

    $upcomingEvents = Event::query()
        ->upcoming()
        ->get();

    expect($upcomingEvents->count())->toBe(1);
});

it('does not get events with a start date of more than 1 year ago', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subYear()->subDay(),
    ]);

    $eventsInTheLastYear = Event::query()
        ->startedInTheLastYear()
        ->get();

    expect($eventsInTheLastYear->count())->toBe(0);
});

it('gets events with a start date in the last year', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subYear()->addDay(),
    ]);

    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subYear()->subDay(),
    ]);

    $eventsInTheLastYear = Event::query()
        ->startedInTheLastYear()
        ->get();

    expect($eventsInTheLastYear->count())->toBe(1);
});

it('gets retreats that are upcoming', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->addDay(),
    ]);

    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subDay(),
    ]);

    $upcomingRetreats = Event::query()
        ->retreats()
        ->upcoming()
        ->get();

    expect($upcomingRetreats->count())->toBe(1);
});

it('gets inactive events', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subDay(),
    ]);

    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subDays(2),
    ]);

    $inactiveEvents = Event::query()
        ->inactive()
        ->get();

    expect($inactiveEvents->count())->toBe(2);
});

it('gets active events', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->addDay(),
    ]);

    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subDay(),
    ]);

    $activeEvents = Event::query()
        ->active()
        ->get();

    expect($activeEvents->count())->toBe(1);
});

it('gets events that were updated this year', function () {
    $now = now();

    Event::factory()->wahRetreat()->create([
        'created_at' => $now->copy()->subYear(),
        'updated_at' => $now->copy()->startOfYear(),
    ]);

    Event::factory()->wahRetreat()->create([
        'updated_at' => $now->copy()->subYear(),
    ]);

    $eventsUpdatedThisYear = Event::query()
        ->whereBetween('updated_at', [$now->copy()->startOfYear(), $now])
        ->get();

    expect($eventsUpdatedThisYear->count())->toBe(1);
});

it('gets events that that occurred this year', function () {
    Event::factory()->wahRetreat()->create([
        'start_date' => now()->subYear(),
    ]);

    Event::factory()->wahRetreat()->create([
        'start_date' => now()->startOfYear(),
    ]);

    $eventsThisYear = Event::query()
        ->whereBetween('start_date', [now()->startOfYear(), now()])
        ->get();

    expect($eventsThisYear->count())->toBe(1);
});

it('gets Fires', function () {
    Event::factory()->fires()->create();

    $events = Event::query()
        ->fires()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Captivating Fires', function () {
    Event::factory()->captivatingFires()->create();

    $events = Event::query()
        ->captivatingFire()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Captivating Core Retreats', function () {
    Event::factory()->captivatingCoreRetreat()->create();

    $events = Event::query()
        ->captivatingRetreat()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Captivating Experiences', function () {
    Event::factory()->captivatingExperience()->create();

    $events = Event::query()
        ->captivatingExperience()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Wild at Heart Retreats', function () {
    Event::factory()->wahRetreat()->create();

    $events = Event::query()
        ->wahRetreat()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Wild at Heart Experiences', function () {
    Event::factory()->wahExperiences()->create();

    $events = Event::query()
        ->wahExperience()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Becoming a King Retreats', function () {
    Event::factory()->becomingAKingRetreat()->create();

    $events = Event::query()
        ->becomingAKingRetreats()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets Becoming a King Experiences', function () {
    Event::factory()->becomingAKingExperience()->create();

    $events = Event::query()
        ->becomingAKingExperiences()
        ->get();

    expect($events->count())->toBe(1);
});
it('gets Survival Schools', function () {
    Event::factory()->survivalSchool()->create();

    $events = Event::query()
        ->survivalSchool()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets events after date', function () {
    Event::factory()->fires()->create([
        'start_date' => now()->subDay(),
    ]);

    $events = Event::query()
        ->beginsAfter(now())
        ->get();

    expect($events->count())->toBe(0);
});

it('does not get private events', function () {
    Event::factory()->fires()->create([
        'is_private' => true,
    ]);

    $events = Event::query()
        ->public()
        ->get();

    expect($events->count())->toBe(0);
});

it('gets approved events only', function () {
    $event1 = Event::factory()->fires()->create([
        'approved_at' => now(),
    ]);

    $event2 = Event::factory()->fires()->create([
        'approved_at' => null,
    ]);

    $events = Event::query()
        ->approved()
        ->get();

    expect($events->count())->toBe(1);
});

it('gets events belonging to user', function () {
    $user = User::factory()->create();
    Event::factory()->fires()->create([
        'user_id' => $user->id,
    ]);

    Event::factory()->fires()->create();

    $events = Event::query()
        ->belongsToUser($user->id)
        ->get();

    expect($events->count())->toBe(1);
});
it('gets events created after', function () {
    $timestamp = '2023-01-01 12:00:00';

    $event = Event::factory()->fires()->create([
        'created_at' => $timestamp,
        'user_id' => User::factory()->create(),
    ]);

    $events = Event::query()
        ->createdAfter($timestamp)
        ->get();

    // Check if found events include our created event
    expect($events->contains('id', $event->id))->toBeTrue()
        ->and($events->count())->toBe(1);
});
