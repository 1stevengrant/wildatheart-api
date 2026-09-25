<?php

use App\Models\User;
use App\Models\EventType;
use Illuminate\Support\Facades\DB;
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

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(12)
        ->and(DB::table('users')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(24)
        ->and(DB::table('event_user_interests')->count())->toBe(72)
        ->and(DB::table('event_user_interests')->whereNotNull('archived_at')->count())->toBe(12)
        ->and(DB::table('attendees')->where('email', 'like', 'test-contact-%@example.test')->count())->toBe(24)
        ->and(DB::table('events')->where('name', 'like', '[TEST]%')->where('attendee_count', 2)->count())->toBe(12);
});

it('is idempotent', function (): void {
    $this->artisan('staging:seed-fixtures')->assertSuccessful();
    $this->artisan('staging:seed-fixtures')->assertSuccessful();

    expect(DB::table('events')->where('name', 'like', '[TEST]%')->count())->toBe(12)
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
