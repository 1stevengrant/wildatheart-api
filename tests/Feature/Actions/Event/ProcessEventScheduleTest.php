<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Carbon\CarbonImmutable;
use Illuminate\Mail\Mailable;
use App\Actions\Event\UpdateEvent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use App\Data\EventScheduleInputData;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Actions\Event\SyncEventSchedule;
use App\Jobs\Fire\ProcessFireScheduleJob;
use App\Models\EventScheduleNotification;
use App\Actions\Event\ProcessEventSchedule;
use App\Jobs\Fire\SendFireCheckinsEmailJob;
use App\Enums\Event\ScheduleNotificationKind;
use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Mail\Fire\Schedule\FireScheduleLastDateMailable;
use App\Mail\Fire\Schedule\FireScheduleDatesAddedMailable;
use App\Mail\Fire\Schedule\FireScheduleUnpublishedMailable;
use App\Mail\Fire\Schedule\FireScheduleAddMoreDatesMailable;

beforeEach(function () {
    Mail::fake();

    // 11:00 in Denver on Tuesday 1 December 2026.
    $this->travelTo(now()->setDate(2026, 12, 1)->setTime(18, 0)->utc());
});

function scheduledFire(array $schedule, array $attributes = []): Event
{
    $event = Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create($attributes));

    SyncEventSchedule::run($event, EventScheduleInputData::from(array_merge([
        'frequency' => 'weekly',
        'timezone' => 'America/Denver',
        'default_start_time' => '19:00',
        'default_end_time' => '21:00',
        'anchor_date' => '2026-12-01',
    ], $schedule)));

    return $event->fresh();
}

function customFire(array $dates, array $attributes = []): Event
{
    return scheduledFire([
        'frequency' => 'varies',
        'anchor_date' => null,
        'dates' => array_map(fn (string $date) => ['date' => $date], $dates),
    ], $attributes);
}

function storedDates(Event $event): array
{
    return $event->scheduleDates()->pluck('date')->map->toDateString()->all();
}

it('does nothing while a weekly fire has more than a week of dates', function () {
    $event = scheduledFire([]);

    expect(ProcessEventSchedule::run($event))->toBeNull();
    Mail::assertNothingQueued();
});

it('adds four dates a week before the last weekly date and emails the hosts once', function () {
    $coHost = User::factory()->create();
    $event = scheduledFire([]);
    $event->managers()->attach($coHost);

    // Last generated date is 22 December; 15 December is seven days before it.
    $this->travelTo(now()->addDays(14));

    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::DatesAdded)
        ->and(storedDates($event))->toBe([
            '2026-12-01', '2026-12-08', '2026-12-15', '2026-12-22',
            '2026-12-29', '2027-01-05', '2027-01-12', '2027-01-19',
        ]);

    Mail::assertQueued(FireScheduleDatesAddedMailable::class, function (FireScheduleDatesAddedMailable $mail) use ($event, $coHost) {
        return $mail->hasTo($event->user->email)
            && $mail->hasCc($coHost->email)
            && $mail->dateLines[0] === 'Tuesday, December 29, 7:00 PM to 9:00 PM MST';
    });

    expect(ProcessEventSchedule::run($event->fresh()))->toBeNull();
    Mail::assertQueuedCount(1);
});

it('catches up after missed runs without filling in the past', function () {
    $event = scheduledFire(['frequency' => 'monthly']);

    // The four dates run December to March; jump past all of them.
    $this->travelTo(now()->addMonths(5));

    ProcessEventSchedule::run($event);

    expect(storedDates($event))->toContain('2027-05-04')
        ->not->toContain('2027-04-06');
});

it('skips unpublished fires', function () {
    $event = scheduledFire([], ['approved_at' => null]);
    $this->travelTo(now()->addDays(20));

    expect(ProcessEventSchedule::run($event))->toBeNull();
    Mail::assertNothingQueued();
});

it('reminds a custom fire two weeks and one week before its last date', function () {
    $event = customFire(['2026-12-20']);

    $this->travelTo(now()->addDays(5));
    expect(ProcessEventSchedule::run($event))->toBe(ScheduleNotificationKind::AddMoreDates);
    expect(ProcessEventSchedule::run($event))->toBeNull();

    $this->travelTo(now()->addDays(7));
    expect(ProcessEventSchedule::run($event))->toBe(ScheduleNotificationKind::LastDate);

    Mail::assertQueued(FireScheduleAddMoreDatesMailable::class, 1);
    Mail::assertQueued(FireScheduleLastDateMailable::class, 1);
});

it('sends only the final reminder when the last date is already within a week', function () {
    $event = customFire(['2026-12-05']);

    expect(ProcessEventSchedule::run($event))->toBe(ScheduleNotificationKind::LastDate);
    Mail::assertNotQueued(FireScheduleAddMoreDatesMailable::class);
});

it('unpublishes a custom fire once its last date has passed, once', function () {
    $event = customFire(['2026-12-03']);

    // Still the 3rd in Denver, so nothing yet.
    $this->travelTo(now()->setDate(2026, 12, 4)->setTime(6, 0));
    ProcessEventSchedule::run($event->fresh());
    expect($event->fresh()->approved_at)->not->toBeNull();

    $this->travelTo(now()->setDate(2026, 12, 4)->setTime(8, 0));
    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::Unpublished);

    $event->refresh();
    expect($event->approved_at)->toBeNull()
        ->and($event->auto_unpublished)->toBeTrue()
        ->and($event->is_private)->toBeFalse();

    Mail::assertQueued(FireScheduleUnpublishedMailable::class, 1);

    // An admin republishes it deliberately; the daily run must not undo that.
    $event->update(['approved_at' => now()]);
    expect(ProcessEventSchedule::run($event->fresh()))->toBeNull()
        ->and($event->fresh()->approved_at)->not->toBeNull();
});

it('starts a fresh reminder cycle when the host adds dates', function () {
    $event = customFire(['2026-12-05']);
    ProcessEventSchedule::run($event);

    SyncEventSchedule::run($event, EventScheduleInputData::from([
        'frequency' => 'varies',
        'timezone' => 'America/Denver',
        'default_start_time' => '19:00',
        'default_end_time' => '21:00',
        'dates' => [['date' => '2026-12-05'], ['date' => '2026-12-12']],
    ]));

    $this->travelTo(now()->addDays(5));
    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::LastDate);
    Mail::assertQueued(FireScheduleLastDateMailable::class, 2);
});

it('dispatches a job per published scheduled fire from the command, captivating fires included', function () {
    Bus::fake();
    $fire = scheduledFire([]);
    $captivatingFire = scheduledFire([], ['event_type_id' => EventType::where('name', EventType::CAPTIVATING_FIRE)->value('id')]);
    Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create());

    $this->artisan('fire:process-schedules')->assertSuccessful();

    Bus::assertDispatchedTimes(ProcessFireScheduleJob::class, 2);
    Bus::assertDispatched(ProcessFireScheduleJob::class, fn (ProcessFireScheduleJob $job) => $job->event->is($captivatingFire));
});

it('leaves scheduled fires out of the 90 day check-in', function () {
    Bus::fake([MonthlyFireCleanupEmailToHostJob::class]);
    $scheduled = scheduledFire([]);
    $unscheduled = Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create());
    Event::query()->whereIn('id', [$scheduled->id, $unscheduled->id])->update(['updated_at' => now()->subDays(120)]);

    (new SendFireCheckinsEmailJob)->handle();

    Bus::assertDispatched(MonthlyFireCleanupEmailToHostJob::class, fn ($job) => $job->event->is($unscheduled));
    Bus::assertNotDispatched(MonthlyFireCleanupEmailToHostJob::class, fn ($job) => $job->event->is($scheduled));
});

it('renders the schedule emails from their templates', function () {
    $event = scheduledFire([], ['name' => 'Denver Fire']);

    $mail = new FireScheduleDatesAddedMailable($event, '2026-12-29', ['Tuesday, December 29, 7:00 PM to 9:00 PM MST']);

    $mail->assertHasSubject('New dates added for Denver Fire')
        ->assertSeeInHtml('Tuesday, December 29, 7:00 PM to 9:00 PM MST')
        ->assertSeeInHtml('#schedule', false)
        ->assertDontSeeInHtml('[button_edit_schedule]', false)
        ->assertDontSeeInHtml('[button_inactive]', false);

    (new FireScheduleUnpublishedMailable($event, '2026-12-29'))
        ->assertSeeInHtml('Tuesday, December 29')
        ->assertDontSeeInHtml('[last_date]', false);
});

it('sends a lapsed custom fire from the keep burning link to the schedule form', function () {
    $event = customFire(['2026-12-03'], ['approved_at' => null]);
    $this->travelTo(now()->addWeek());

    $this->get(URL::signedRoute('fire.burn', ['event' => $event]))
        ->assertRedirect($event->scheduleEditUrl());

    expect($event->fresh()->approved_at)->toBeNull();
});

it('keeps a scheduled fire frequency when edited through the old form', function () {
    $event = scheduledFire([]);

    UpdateEvent::run(['frequency' => 'biweekly', 'start_date' => now()->addMonth(), 'name' => 'Renamed'], $event);

    $event->refresh();
    expect($event->frequency->value)->toBe('weekly')
        ->and($event->start_date)->toBeNull()
        ->and($event->name)->toBe('Renamed');
});

it('refuses to republish a lapsed custom fire from the old dashboard toggle', function () {
    $event = customFire(['2026-12-03'], ['approved_at' => null]);
    $this->travelTo(now()->addWeek());

    $this->actingAs($event->user)
        ->put(route('toggle-event-approved-at', $event))
        ->assertSessionHasErrors('schedule');

    expect($event->fresh()->approved_at)->toBeNull();
});

it('renews a monthly fire on the same weekday position a week before its last date', function () {
    // Second Tuesday: 8 December, 12 January, 9 February, 9 March.
    $event = scheduledFire(['frequency' => 'monthly', 'anchor_date' => '2026-12-08']);

    $this->travelTo(CarbonImmutable::parse('2027-03-01 18:00', 'UTC'));
    expect(ProcessEventSchedule::run($event->fresh()))->toBeNull();

    $this->travelTo(CarbonImmutable::parse('2027-03-02 18:00', 'UTC'));
    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::DatesAdded)
        ->and(storedDates($event))->toBe([
            '2026-12-08', '2027-01-12', '2027-02-09', '2027-03-09',
            '2027-04-13', '2027-05-11', '2027-06-08', '2027-07-13',
        ]);
});

it('renews a fifth weekday monthly fire on the last weekday of each month', function () {
    // 29 December is the fifth Tuesday, so the series is the last Tuesday of each month.
    $event = scheduledFire(['frequency' => 'monthly', 'anchor_date' => '2026-12-29']);

    expect(storedDates($event))->toBe(['2026-12-29', '2027-01-26', '2027-02-23', '2027-03-30']);

    $this->travelTo(CarbonImmutable::parse('2027-03-23 18:00', 'UTC'));
    expect(ProcessEventSchedule::run($event->fresh()))->toBe(ScheduleNotificationKind::DatesAdded)
        ->and(array_slice(storedDates($event), 4))->toBe(['2027-04-27', '2027-05-25', '2027-06-29', '2027-07-27']);

    Mail::assertQueued(FireScheduleDatesAddedMailable::class, function (FireScheduleDatesAddedMailable $mail) {
        return $mail->dateLines[0] === 'Tuesday, April 27, 7:00 PM to 9:00 PM MDT';
    });
});

dataset('schedule emails', [
    'weekly renewal' => [fn (array $attributes) => scheduledFire([], $attributes), 14, FireScheduleDatesAddedMailable::class],
    'monthly renewal' => [fn (array $attributes) => scheduledFire(['frequency' => 'monthly'], $attributes), 84, FireScheduleDatesAddedMailable::class],
    'custom two week reminder' => [fn (array $attributes) => customFire(['2026-12-20'], $attributes), 5, FireScheduleAddMoreDatesMailable::class],
    'custom final reminder' => [fn (array $attributes) => customFire(['2026-12-05'], $attributes), 0, FireScheduleLastDateMailable::class],
    'custom unpublish' => [fn (array $attributes) => customFire(['2026-12-03'], $attributes), 3, FireScheduleUnpublishedMailable::class],
]);

dataset('fire types', [
    'Fires' => [EventType::FIRES],
    'Captivating Fires' => [EventType::CAPTIVATING_FIRE],
]);

it('sends each schedule email once to the owner, CCs co-hosts and leaves out pending invitees', function (Closure $makeFire, int $daysLater, string $mailable, string $eventTypeName) {
    $event = $makeFire(['event_type_id' => EventType::where('name', $eventTypeName)->value('id')]);
    $coHost = User::factory()->create();
    $event->managers()->attach($coHost);
    EventManagerInvitation::create([
        'event_id' => $event->id,
        'email' => 'pending-invitee@example.com',
        'invited_by_user_id' => $event->user_id,
    ]);
    $datesBefore = count(storedDates($event));

    $this->travelTo(now()->addDays($daysLater));
    ProcessFireScheduleJob::dispatchSync($event->fresh());
    ProcessFireScheduleJob::dispatchSync($event->fresh());

    Mail::assertQueuedCount(1);
    Mail::assertQueued($mailable, function (Mailable $mail) use ($event, $coHost) {
        return $mail->hasTo($event->user->email)
            && $mail->hasCc($coHost->email)
            && ! $mail->hasCc('pending-invitee@example.com')
            && ! $mail->hasTo('pending-invitee@example.com');
    });

    $addedDates = $mailable === FireScheduleDatesAddedMailable::class ? 4 : 0;

    expect(count(storedDates($event)))->toBe($datesBefore + $addedDates)
        ->and(EventScheduleNotification::where('event_id', $event->id)->count())->toBe(1);
})->with('schedule emails')->with('fire types');

dataset('fire timezones', [
    // 15:00 UTC is 04:00 the next day in Auckland, so it acts a day before UTC would.
    'Auckland custom' => ['Pacific/Auckland', 'varies', [
        ['2026-12-08 15:00', ScheduleNotificationKind::AddMoreDates],
        ['2026-12-09 15:00', ScheduleNotificationKind::LastDate],
        ['2026-12-16 15:00', null],
        ['2026-12-17 15:00', ScheduleNotificationKind::Unpublished],
    ]],
    'Auckland weekly' => ['Pacific/Auckland', 'weekly', [
        ['2026-12-20 15:00', null],
        ['2026-12-21 15:00', ScheduleNotificationKind::DatesAdded],
    ]],
    // 15:00 UTC is 05:00 the same day in Honolulu.
    'Honolulu custom' => ['Pacific/Honolulu', 'varies', [
        ['2026-12-09 15:00', ScheduleNotificationKind::AddMoreDates],
        ['2026-12-10 15:00', ScheduleNotificationKind::LastDate],
        ['2026-12-17 15:00', null],
        ['2026-12-18 15:00', ScheduleNotificationKind::Unpublished],
    ]],
    'Honolulu weekly' => ['Pacific/Honolulu', 'weekly', [
        ['2026-12-21 15:00', null],
        ['2026-12-22 15:00', ScheduleNotificationKind::DatesAdded],
    ]],
]);

it('judges reminders, renewals and lapses by the fire\'s own calendar day at 15:00 UTC', function (string $timezone, string $frequency, array $runs) {
    // Weekly: 8, 15, 22 and 29 December. Custom: a single last date of 17 December.
    $event = scheduledFire($frequency === 'weekly'
        ? ['timezone' => $timezone, 'anchor_date' => '2026-12-08']
        : ['timezone' => $timezone, 'frequency' => 'varies', 'anchor_date' => null, 'dates' => [['date' => '2026-12-17']]]);

    foreach ($runs as [$utc, $expected]) {
        $this->travelTo(CarbonImmutable::parse($utc, 'UTC'));

        expect(ProcessEventSchedule::run($event->fresh()))->toBe($expected, "Run at {$utc} UTC");
    }
})->with('fire timezones');
