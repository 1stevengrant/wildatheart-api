<?php

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use App\Data\EventScheduleInputData;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Actions\Event\SyncEventSchedule;
use App\Actions\Event\ProcessEventSchedule;
use App\Enums\Event\ScheduleNotificationKind;

beforeEach(function () {
    Queue::fake();
    Bus::fake();
    Mail::fake();
    cache()->flush();

    // 11:00 in Denver on Tuesday 1 December 2026.
    $this->travelTo(now()->setDate(2026, 12, 1)->setTime(18, 0)->utc());
});

function publishedScheduledFire(array $dates): Event
{
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create(['is_private' => false]));

    SyncEventSchedule::run($event, EventScheduleInputData::from([
        'frequency' => 'varies',
        'timezone' => 'America/Denver',
        'default_start_time' => '19:00',
        'default_end_time' => '21:00',
        'dates' => array_map(fn (string $date) => ['date' => $date], $dates),
    ]));

    return $event;
}

it('shows the next date and upcoming dates on the event detail', function () {
    $event = publishedScheduledFire(['2026-12-01', '2026-12-15']);

    $this->getJson(route('v1.events.events.show', $event))
        ->assertOk()
        ->assertJsonPath('nextDate', [
            'date' => '2026-12-01',
            'startTime' => '19:00',
            'endTime' => '21:00',
            'startsAt' => '2026-12-02T02:00:00Z',
            'endsAt' => '2026-12-02T04:00:00Z',
            'timezone' => 'America/Denver',
            'timezoneAbbreviation' => 'MST',
        ])
        ->assertJsonCount(2, 'upcomingDates');
});

it('moves to the following date once a date has ended', function () {
    $event = publishedScheduledFire(['2026-12-01', '2026-12-15']);

    $this->travelTo(now()->setTime(23, 59)->addHours(5));

    $this->getJson(route('v1.events.events.show', $event))
        ->assertOk()
        ->assertJsonPath('nextDate.date', '2026-12-15')
        ->assertJsonCount(1, 'upcomingDates');
});

it('returns null dates for fires without a schedule', function () {
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create());

    $this->getJson(route('v1.events.events.show', $event))
        ->assertOk()
        ->assertJsonPath('nextDate', null)
        ->assertJsonPath('upcomingDates', []);
});

it('includes the next date in the events index', function () {
    $event = publishedScheduledFire(['2026-12-15']);

    $indexed = collect($this->getJson(route('v1.events.events.index'))->assertOk()->json())->keyBy('id');

    expect($indexed[$event->id]['nextDate']['date'])->toBe('2026-12-15');
});

function searchedFire(array $schedule): Event
{
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create([
        'is_private' => false,
        'latitude' => 45.6770,
        'longitude' => -111.0429,
    ]));

    SyncEventSchedule::run($event, EventScheduleInputData::from(array_merge([
        'frequency' => 'weekly',
        'timezone' => 'America/Denver',
        'default_start_time' => '19:00',
        'default_end_time' => '21:00',
        'anchor_date' => '2026-12-01',
    ], $schedule)));

    return $event;
}

/**
 * @return array{index: ?array, search: ?array}
 */
function publicListings(Event $event): array
{
    $index = collect(test()->getJson(route('v1.events.events.index'))->assertOk()->json())->firstWhere('id', $event->id);
    $search = collect(test()->getJson(route('v1.events.search', [
        'latitude' => 45.6770,
        'longitude' => -111.0429,
        'nearest' => 100,
    ]))->assertOk()->json('events'))->firstWhere('id', $event->id);

    return ['index' => $index, 'search' => $search];
}

it('shows renewed dates in the cached index and search straight after a renewal', function () {
    // Weekly from 1 December; every date has ended by the 23rd, so the next date is gone.
    $event = searchedFire([]);
    $this->travelTo(CarbonImmutable::parse('2026-12-23 18:00', 'UTC'));

    $before = publicListings($event);
    expect($before['index']['nextDate'])->toBeNull()
        ->and($before['search']['nextDate'])->toBeNull();

    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::DatesAdded);

    $after = publicListings($event);
    expect($after['index']['nextDate']['date'])->toBe('2026-12-29')
        ->and($after['search']['nextDate']['date'])->toBe('2026-12-29');

    $this->getJson(route('v1.events.events.show', $event))
        ->assertJsonPath('nextDate.date', '2026-12-29')
        ->assertJsonCount(4, 'upcomingDates');
});

it('drops a lapsed fire from the cached index and search straight after it is unpublished', function () {
    $event = searchedFire(['frequency' => 'varies', 'anchor_date' => null, 'dates' => [['date' => '2026-12-03']]]);
    $this->travelTo(CarbonImmutable::parse('2026-12-04 18:00', 'UTC'));

    $before = publicListings($event);
    expect($before['index'])->not->toBeNull()
        ->and($before['search'])->not->toBeNull();

    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::Unpublished);

    expect(publicListings($event))->toBe(['index' => null, 'search' => null]);
});
