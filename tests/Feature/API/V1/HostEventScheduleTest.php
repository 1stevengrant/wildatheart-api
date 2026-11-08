<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EventSchedule;
use App\Models\EventScheduleDate;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Support\Facades\Bus;
use App\Data\EventScheduleInputData;
use Illuminate\Support\Facades\Mail;
use App\Actions\Event\SyncEventSchedule;

beforeEach(function () {
    Mail::fake();
    Bus::fake();

    // 11:00 in Denver on Tuesday 1 December 2026.
    $this->travelTo(now()->setDate(2026, 12, 1)->setTime(18, 0)->utc());

    $this->host = User::factory()->create(['email' => 'host@wildatheart.org']);
    $this->fireType = EventType::where('name', EventType::FIRES)->firstOrFail();
    $this->fireType->update(['allow_materials_order' => false]);

    mockJwtAuthentication($this->host);
});

/**
 * @return array<string, mixed>
 */
function schedulePayload(array $overrides = []): array
{
    return array_merge([
        'frequency' => 'weekly',
        'timezone' => 'America/Denver',
        'default_start_time' => '19:00',
        'default_end_time' => '21:00',
        'anchor_date' => '2026-12-08',
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function scheduledFireBody(array $overrides = []): array
{
    return array_merge([
        'name' => 'Denver, CO',
        'city' => 'Denver',
        'zip' => '80202',
        'country' => 'US',
        'latitude' => 39.7392,
        'longitude' => -104.9903,
        'state' => 'CO',
        'is_private' => false,
        'number_of_items_in_material_order' => '0',
        'schedule' => schedulePayload(),
    ], $overrides);
}

function createFireDraft(User $host, array $attributes = []): Event
{
    return Event::withoutEvents(fn () => Event::factory()->fires()->for($host)->create(array_merge([
        'approved_at' => null,
    ], $attributes)));
}

it('saves a weekly schedule on a new draft and generates four dates', function () {
    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody())
        ->assertOk()
        ->assertJsonPath('event.schedule.frequency', 'weekly')
        ->assertJsonPath('event.schedule.pattern_label', 'Every Tuesday')
        ->assertJsonPath('event.schedule.auto_renews', true)
        ->assertJsonPath('event.schedule.last_date', '2026-12-29')
        ->assertJsonCount(4, 'event.schedule.dates')
        ->assertJsonPath('event.schedule.dates.0', [
            'date' => '2026-12-08',
            'start_time' => '19:00',
            'end_time' => '21:00',
            'starts_at' => '2026-12-09T02:00:00Z',
            'ends_at' => '2026-12-09T04:00:00Z',
            'has_custom_times' => false,
            'is_past' => false,
        ]);

    $event = Event::findOrFail($response->json('event.id'));

    expect($event->frequency)->toBe(FrequencyEnum::WEEKLY)
        ->and($event->start_date)->toBeNull()
        ->and($event->approved_at)->toBeNull();
});

it('keeps local wall-clock times across a daylight saving change', function () {
    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody([
            'schedule' => schedulePayload([
                'frequency' => 'varies',
                'anchor_date' => null,
                'dates' => [
                    ['date' => '2027-03-02'],
                    ['date' => '2027-03-16', 'start_time' => '18:30', 'end_time' => '20:30'],
                ],
            ]),
        ]))
        ->assertOk()
        ->assertJsonPath('event.schedule.dates.0.starts_at', '2027-03-03T02:00:00Z')
        ->assertJsonPath('event.schedule.dates.1.start_time', '18:30')
        ->assertJsonPath('event.schedule.dates.1.starts_at', '2027-03-17T00:30:00Z')
        ->assertJsonPath('event.schedule.dates.1.has_custom_times', true)
        ->assertJsonPath('event.schedule.needs_more_dates', false);
});

it('replaces upcoming dates and leaves past dates alone', function () {
    $event = createFireDraft($this->host);
    $past = EventScheduleDate::factory()->for($event)->create([
        'date' => '2026-11-24',
        'starts_at' => '2026-11-25 02:00:00',
        'ends_at' => '2026-11-25 04:00:00',
    ]);

    SyncEventSchedule::run($event, EventScheduleInputData::from(schedulePayload()));

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody([
            'event_id' => $event->id,
            'schedule' => schedulePayload([
                'dates' => [
                    ['date' => '2026-11-24', 'start_time' => '10:00', 'end_time' => '11:00'],
                    ['date' => '2026-12-08'],
                    ['date' => '2026-12-17'],
                ],
            ]),
        ]))
        ->assertOk();

    expect($event->scheduleDates()->pluck('date')->map->toDateString()->all())
        ->toBe(['2026-11-24', '2026-12-08', '2026-12-17'])
        ->and($past->fresh()->start_time)->toBeNull();
});

it('clears start and end dates when a schedule is saved', function () {
    $event = createFireDraft($this->host, [
        'start_date' => '2027-01-10 19:00:00',
        'end_date' => '2027-01-10 21:00:00',
        'frequency' => FrequencyEnum::MONTHLY,
    ]);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody([
            'event_id' => $event->id,
        ]))
        ->assertOk();

    $event->refresh();

    expect($event->start_date)->toBeNull()
        ->and($event->end_date)->toBeNull()
        ->and($event->frequency)->toBe(FrequencyEnum::WEEKLY);
});

it('keeps a scheduled fire frequency when a save omits the schedule', function () {
    $event = createFireDraft($this->host);
    SyncEventSchedule::run($event, EventScheduleInputData::from(schedulePayload()));

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", [
            'event_id' => $event->id,
            'frequency' => 'biweekly',
            'start_date' => '2027-01-10',
        ])
        ->assertOk();

    $event->refresh();

    expect($event->frequency)->toBe(FrequencyEnum::WEEKLY)
        ->and($event->start_date)->toBeNull();
});

it('publishes a fire with a schedule and no start date', function () {
    $draft = createFireDraft($this->host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$this->fireType->slug}/events/{$draft->id}/publish",
            scheduledFireBody(),
        )
        ->assertOk()
        ->assertJsonPath('event.schedule.last_date', '2026-12-29');

    expect($draft->fresh()->approved_at)->not->toBeNull();
});

it('still requires start date and frequency for a fire without a schedule', function () {
    $draft = createFireDraft($this->host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$this->fireType->slug}/events/{$draft->id}/publish",
            scheduledFireBody(['schedule' => null]),
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'frequency']);
});

it('requires an upcoming date to publish a custom schedule', function () {
    $draft = createFireDraft($this->host);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson(
            "/api/v1/host/event-types/{$this->fireType->slug}/events/{$draft->id}/publish",
            scheduledFireBody([
                'schedule' => schedulePayload([
                    'frequency' => 'varies',
                    'anchor_date' => null,
                    'dates' => [['date' => '2026-11-20']],
                ]),
            ]),
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['schedule.dates']);
});

it('lets a draft save a custom schedule with no dates yet', function () {
    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody([
            'schedule' => schedulePayload(['frequency' => 'varies', 'anchor_date' => null, 'dates' => []]),
        ]))
        ->assertOk()
        ->assertJsonPath('event.schedule.dates', [])
        ->assertJsonPath('event.schedule.needs_more_dates', true);
});

it('requires an upcoming date when editing a listed fire', function () {
    $event = createFireDraft($this->host, ['approved_at' => now()]);

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody([
            'event_id' => $event->id,
            'schedule' => schedulePayload(['frequency' => 'varies', 'anchor_date' => null, 'dates' => []]),
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['schedule.dates']);
});

it('validates schedule fields', function (array $schedule, string $error) {
    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events/draft", scheduledFireBody([
            'schedule' => schedulePayload($schedule),
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$error]);
})->with([
    'legacy frequency' => [['frequency' => 'biweekly'], 'schedule.frequency'],
    'bad timezone' => [['timezone' => 'Mars/Olympus'], 'schedule.timezone'],
    'end before start' => [['default_end_time' => '18:00'], 'schedule.default_end_time'],
    'weekly without anchor' => [['anchor_date' => null], 'schedule.anchor_date'],
    'more than a year ahead' => [['frequency' => 'varies', 'anchor_date' => null, 'dates' => [['date' => '2028-01-05']]], 'schedule.dates'],
    'duplicate dates' => [['dates' => [['date' => '2026-12-08'], ['date' => '2026-12-08']]], 'schedule.dates.0.date'],
    'override end before start' => [['dates' => [['date' => '2026-12-08', 'start_time' => '20:00', 'end_time' => '19:00']]], 'schedule.dates.0.end_time'],
    'too many dates' => [['frequency' => 'varies', 'anchor_date' => null, 'dates' => collect(range(1, 25))->map(fn (int $week) => ['date' => now()->addWeeks($week)->toDateString()])->all()], 'schedule.dates'],
]);

it('rejects a schedule for event types that are not fires', function () {
    $retreat = EventType::where('group_type', EventType::TYPE_RETREAT)->firstOrFail();

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$retreat->slug}/events/draft", scheduledFireBody())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['schedule']);
});

it('creates a scheduled fire through the store endpoint', function () {
    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/event-types/{$this->fireType->slug}/events", scheduledFireBody())
        ->assertOk()
        ->assertJsonPath('event.schedule.frequency', 'weekly')
        ->assertJsonCount(4, 'event.schedule.dates');

    expect(EventSchedule::where('event_id', $response->json('event.id'))->exists())->toBeTrue();
});

it('includes the schedule on the host events index', function () {
    $event = createFireDraft($this->host);
    SyncEventSchedule::run($event, EventScheduleInputData::from(schedulePayload(['frequency' => 'monthly'])));
    $unscheduled = createFireDraft($this->host);

    $drafts = collect($this->withHeaders(jwtAuthenticationHeaders())
        ->getJson('/api/v1/host/events')
        ->assertOk()
        ->json('drafts'))
        ->keyBy('id');

    expect($drafts[$event->id]['schedule']['pattern_label'])->toBe('2nd Tuesday of the month')
        ->and($drafts[$unscheduled->id]['schedule'])->toBeNull();
});

it('adds the next four dates when a lapsed weekly fire is republished', function () {
    $event = createFireDraft($this->host);
    SyncEventSchedule::run($event, EventScheduleInputData::from(schedulePayload()));

    $this->travelTo(now()->addMonths(2));

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertOk()
        ->assertJsonPath('event.schedule.dates.0.date', '2027-02-02')
        ->assertJsonCount(4, 'event.schedule.dates');
});

it('refuses to republish a custom fire with no upcoming date', function () {
    $event = createFireDraft($this->host);
    SyncEventSchedule::run($event, EventScheduleInputData::from(schedulePayload([
        'frequency' => 'varies',
        'anchor_date' => null,
        'dates' => [['date' => '2026-12-03']],
    ])));

    $this->travelTo(now()->addWeek());

    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson("/api/v1/host/events/{$event->id}/publish")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['schedule']);

    expect($event->fresh()->approved_at)->toBeNull();
});

it('previews dates for an anchor', function () {
    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/schedule-previews', [
            'frequency' => 'monthly',
            'anchor_date' => '2026-12-29',
        ])
        ->assertOk()
        ->assertExactJson([
            'dates' => ['2026-12-29', '2027-01-26', '2027-02-23', '2027-03-30'],
            'pattern_label' => 'Last Tuesday of the month',
        ]);
});

it('rejects previews for custom schedules', function () {
    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/schedule-previews', [
            'frequency' => 'varies',
            'anchor_date' => '2026-12-29',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['frequency']);
});
