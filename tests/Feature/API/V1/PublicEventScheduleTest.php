<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use App\Data\EventScheduleInputData;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Actions\Event\SyncEventSchedule;

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
