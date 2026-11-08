<?php

use App\Models\User;
use App\Models\Event;
use App\Actions\Event\UpdateEvent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use App\Data\EventScheduleInputData;
use Illuminate\Support\Facades\Mail;
use App\Actions\Event\SyncEventSchedule;
use App\Jobs\Fire\ProcessFireScheduleJob;
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

it('dispatches a job per published scheduled fire from the command', function () {
    Bus::fake();
    $scheduled = scheduledFire([]);
    Event::withoutEvents(fn () => Event::factory()->fires()->approved()->create());

    $this->artisan('fire:process-schedules')->assertSuccessful();

    Bus::assertDispatchedTimes(ProcessFireScheduleJob::class, 1);
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
