<?php

use App\Models\User;
use App\Models\Event;
use Carbon\CarbonImmutable;
use App\Data\EventScheduleInputData;
use App\Actions\Event\SyncEventSchedule;
use Ghijk\EmailPreview\Models\CapturedEmail;

beforeEach(function () {
    // 11:00 in Denver on Tuesday 1 December 2026.
    $this->travelTo(CarbonImmutable::parse('2026-12-01 18:00', 'UTC'));

    $this->admin = User::factory()->create(['super' => true]);
});

function testingFire(array $schedule = [], array $attributes = []): Event
{
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create($attributes));

    SyncEventSchedule::run($event, EventScheduleInputData::from(array_merge([
        'frequency' => 'varies',
        'timezone' => 'America/Denver',
        'default_start_time' => '19:00',
        'default_end_time' => '21:00',
        'dates' => [
            ['date' => '2026-12-03'],
            ['date' => '2026-12-17', 'start_time' => '18:30', 'end_time' => '20:00'],
        ],
    ], $schedule)));

    return $event->fresh();
}

function testingFireDates(Event $event): array
{
    return $event->scheduleDates()->get()
        ->map(fn ($date) => [$date->date->toDateString(), $date->start_time, $date->end_time])
        ->all();
}

it('is hidden in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->actingAs($this->admin)
        ->get(route('internal.fire-schedules.index'))
        ->assertNotFound();
});

it('is for super admins only', function () {
    $this->actingAs(User::factory()->create(['super' => false]))
        ->get(route('internal.fire-schedules.index'))
        ->assertForbidden();
});

it('lists scheduled fires and finds them by name, id or host email', function () {
    $fire = testingFire(attributes: ['name' => 'Jamie Test Fire']);
    $other = testingFire(attributes: ['name' => 'Another Fire']);
    Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create(['name' => 'Unscheduled Fire']));

    $this->actingAs($this->admin)
        ->get(route('internal.fire-schedules.index'))
        ->assertOk()
        ->assertSee('Jamie Test Fire')
        ->assertSee('Another Fire')
        ->assertDontSee('Unscheduled Fire')
        ->assertSee('Thu 17 Dec 2026, 6:30 PM to 8:00 PM MST');

    foreach (['Jamie', $fire->id, $fire->user->email] as $search) {
        $this->actingAs($this->admin)
            ->get(route('internal.fire-schedules.index', ['search' => $search]))
            ->assertSee('Jamie Test Fire')
            ->assertDontSee($other->name);
    }
});

it('moves every date by whole weeks, keeping weekdays and per-date times', function () {
    $fire = testingFire();

    // The last date is 16 days away; one week earlier puts it 9 days away.
    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.moves.store', $fire), ['preset' => 'two_week_reminder'])
        ->assertSessionHas('status', fn (string $status) => str_contains($status, 'moved every date by -7 days'));

    expect(testingFireDates($fire))->toBe([
        ['2026-11-26', null, null],
        ['2026-12-10', '18:30:00', '20:00:00'],
    ]);
});

it('moves a weekly fire\'s anchor with its dates so renewals follow the moved series', function () {
    $fire = testingFire(['frequency' => 'weekly', 'anchor_date' => '2026-12-01', 'dates' => null]);

    // Weekly from 1 December, so the last date is 22 December, 21 days away. Two weeks
    // earlier puts it 7 days away, the edge of the final week.
    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.moves.store', $fire), ['preset' => 'final_week']);

    expect($fire->schedule->fresh()->anchor_date->toDateString())->toBe('2026-11-17')
        ->and(array_column(testingFireDates($fire), 0))->toBe(['2026-11-17', '2026-11-24', '2026-12-01', '2026-12-08']);
});

it('runs the check for one fire and captures the email, whatever the mailer', function () {
    config(['mail.default' => 'log']);
    $fire = testingFire();
    $coHost = User::factory()->create();
    $fire->managers()->attach($coHost);
    $untouched = testingFire();

    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.moves.store', $fire), ['preset' => 'two_week_reminder']);

    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.checks.store', $fire))
        ->assertSessionHas('status', "{$fire->name}: Sent the two week reminder to {$fire->user->email}, CC {$coHost->email}.")
        ->assertSessionHas('showCapturedEmailsLink', true);

    $captured = CapturedEmail::sole();

    expect($captured->subject)->toBe("Add more dates for {$fire->name}")
        ->and($captured->to)->toBe([$fire->user->email])
        ->and($captured->cc)->toBe([$coHost->email])
        ->and($untouched->scheduleNotifications()->exists())->toBeFalse();

    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.checks.store', $fire))
        ->assertSessionHas('status', fn (string $status) => str_contains($status, 'Nothing to do'));

    expect(CapturedEmail::count())->toBe(1);
});

it('lapses a fire, then resets it so the scenario can run again', function () {
    $fire = testingFire();

    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.moves.store', $fire), ['preset' => 'lapsed']);
    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.checks.store', $fire))
        ->assertSessionHas('status', fn (string $status) => str_contains($status, 'Unpublished the Fire'));

    expect($fire->fresh()->approved_at)->toBeNull()
        ->and(CapturedEmail::sole()->subject)->toBe("{$fire->name} is no longer on the map");

    $this->actingAs($this->admin)
        ->get(route('internal.fire-schedules.index'))
        ->assertSee('Unpublished by the schedule')
        ->assertSee('Unpublished for last date');

    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.checks.store', $fire))
        ->assertSessionHas('status', fn (string $status) => str_contains($status, 'skips unpublished Fires'));

    $this->actingAs($this->admin)
        ->delete(route('internal.fire-schedules.notifications.destroy', $fire));

    expect($fire->fresh()->approved_at)->not->toBeNull()
        ->and($fire->fresh()->auto_unpublished)->toBeFalse()
        ->and($fire->scheduleNotifications()->exists())->toBeFalse();

    $this->actingAs($this->admin)
        ->post(route('internal.fire-schedules.checks.store', $fire));

    expect(CapturedEmail::count())->toBe(2);
});
