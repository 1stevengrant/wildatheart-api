<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\EventScheduleNotification;
use App\Actions\Event\ProcessEventSchedule;
use App\Enums\Event\ScheduleNotificationKind;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.env' => 'local']);

    foreach ([EventType::FIRES, EventType::WAH_RETREAT, EventType::CAPTIVATING_EXPERIENCE] as $name) {
        EventType::factory()->create(['name' => $name, 'is_active' => true]);
    }

    foreach ([
        'jamiemorrison@wildatheart.org',
        'steven@ghijk.co.uk',
        'wookie@wildatheart.org',
        'nic@wildatheart.org',
    ] as $email) {
        User::factory()->create(['email' => $email]);
    }
});

it('seeds complete fixtures for all configured hosts', function (): void {
    $this->artisan('staging:seed-fixtures')
        ->expectsOutputToContain('seeded successfully')
        ->assertSuccessful();

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(15)
        ->and(DB::table('users')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(24)
        ->and(DB::table('event_user_interests')->count())->toBe(72)
        ->and(DB::table('event_user_interests')->whereNotNull('archived_at')->count())->toBe(12)
        ->and(DB::table('attendees')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(24)
        ->and(DB::table('events')->where('name', 'like', '[TEST]%')->where('attendee_count', 2)->count())->toBe(12);
});

it('is idempotent', function (): void {
    $this->artisan('staging:seed-fixtures')->assertSuccessful();
    $this->artisan('staging:seed-fixtures')->assertSuccessful();

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(15)
        ->and(DB::table('users')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(24)
        ->and(DB::table('event_user_interests')->count())->toBe(72)
        ->and(DB::table('attendees')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(24);
});

it('supports a dry run without writing fixtures', function (): void {
    $this->artisan('staging:seed-fixtures', ['--dry-run' => true])
        ->expectsOutputToContain('Dry run')
        ->assertSuccessful();

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(0)
        ->and(DB::table('users')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(0);
});

it('rejects production and testing environments', function (string $environment): void {
    config(['app.env' => $environment]);

    $this->artisan('staging:seed-fixtures')
        ->expectsOutputToContain('only allowed in local and staging')
        ->assertFailed();

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(0);
})->with(['production', 'testing']);

it('fails and rolls back when a configured host is missing', function (): void {
    User::query()->where('email', 'nic@wildatheart.org')->delete();

    $this->artisan('staging:seed-fixtures')
        ->expectsOutputToContain('Staging fixture host not found')
        ->assertFailed();

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(0)
        ->and(DB::table('users')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(0);
});

function scheduledFixtureFires()
{
    return Event::query()
        ->where('name', 'like', '[TEST] % scheduled Fire hosted by jamie')
        ->with(['schedule', 'user', 'managers'])
        ->orderBy('name')
        ->get();
}

it('seeds a Weekly, Monthly and Custom scheduled Fire for Jamie, each with a co-host', function (): void {
    $this->artisan('staging:seed-fixtures')->assertSuccessful();

    $fires = scheduledFixtureFires()->keyBy(fn (Event $fire) => $fire->schedule->frequency->value);

    expect($fires->keys()->sort()->values()->all())->toBe(['monthly', 'varies', 'weekly'])
        ->and($fires->every(fn (Event $fire) => $fire->user->email === 'jamiemorrison@wildatheart.org'))->toBeTrue()
        ->and($fires->every(fn (Event $fire) => $fire->isApproved()))->toBeTrue()
        ->and($fires->every(fn (Event $fire) => $fire->scheduleDates()->count() === 4))->toBeTrue()
        ->and($fires->map(fn (Event $fire) => $fire->managers->pluck('email')->all())->all())->toEqual([
            'weekly' => ['steven@ghijk.co.uk'],
            'monthly' => ['wookie@wildatheart.org'],
            'varies' => ['nic@wildatheart.org'],
        ])
        ->and($fires->map(fn (Event $fire) => $fire->schedule->timezone)->all())->toEqual([
            'weekly' => 'America/Denver',
            'monthly' => 'America/Chicago',
            'varies' => 'Pacific/Auckland',
        ]);
});

it('seeds the scheduled Fires where the daily check does nothing', function (string $now): void {
    $this->travelTo($now);
    Mail::fake();

    $this->artisan('staging:seed-fixtures')->assertSuccessful();

    scheduledFixtureFires()->each(function (Event $fire): void {
        $daysUntilLastDate = (int) $fire->schedule->localToday()->diffInDays($fire->scheduleDates()->max('date'));

        expect(ProcessEventSchedule::run($fire))->toBeNull()
            ->and($daysUntilLastDate)->toBeGreaterThan(ProcessEventSchedule::ADD_MORE_WITHIN_DAYS);
    });

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
})->with([
    'UTC midday' => '2026-11-11 12:00:00',
    'Auckland already tomorrow' => '2026-11-11 23:30:00',
]);

it('puts moved scheduled Fires back in the quiet window when run again', function (): void {
    $this->artisan('staging:seed-fixtures')->assertSuccessful();

    $custom = scheduledFixtureFires()->firstWhere('schedule.frequency.value', 'varies');
    $custom->scheduleDates()->delete();
    $custom->scheduleDates()->create([
        'date' => now()->subDays(4)->toDateString(),
        'starts_at' => now()->subDays(4),
        'ends_at' => now()->subDays(4)->addHours(2),
    ]);
    EventScheduleNotification::create([
        'event_id' => $custom->id,
        'kind' => ScheduleNotificationKind::Unpublished,
        'last_date' => now()->subDays(4)->toDateString(),
    ]);
    $custom->update(['approved_at' => null, 'auto_unpublished' => true, 'auto_unpublished_at' => now()]);

    $this->artisan('staging:seed-fixtures')->assertSuccessful();

    $custom->refresh();

    expect(scheduledFixtureFires())->toHaveCount(3)
        ->and($custom->isApproved())->toBeTrue()
        ->and($custom->auto_unpublished)->toBeFalse()
        ->and($custom->scheduleNotifications()->count())->toBe(0)
        ->and($custom->scheduleDates()->count())->toBe(4)
        ->and($custom->scheduleDates()->min('date'))->toBeGreaterThan(now()->toDateString())
        ->and($custom->managers()->count())->toBe(1);
});
