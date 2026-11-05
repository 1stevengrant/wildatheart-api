<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Event;
use App\Jobs\Admin\GenerateAndEmailReport;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\ArchiveActivityLogCommand;
use App\Console\Commands\WeGive\UpdateDonorIdCommand;
use App\Console\Commands\Fire\SendFiresCheckinsEmailCommand;

function scheduledEvent(string $name): Event
{
    return collect(app(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains("{$event->command} {$event->description}", $name));
}

function globalSetRow(string $handle, string $data, string $updatedAt): array
{
    return [
        'handle' => $handle,
        'locale' => 'default',
        'data' => $data,
        'created_at' => $updatedAt,
        'updated_at' => $updatedAt,
    ];
}

describe('copying content', function (): void {
    beforeEach(function (): void {
        config(['app.env' => 'staging']);

        $this->source = 'wahlocal_test_source_' . getmypid();
        $target = DB::getDatabaseName();

        config(['database.connections.staging_content_source' => config('database.connections.mysql')]);
        $this->sourceConnection = DB::connection('staging_content_source');
        $this->sourceConnection->statement('set session lock_wait_timeout = 10');
        $this->sourceConnection->statement("create database `{$this->source}`");

        foreach ([...config('dbsync.contentTables'), 'users'] as $table) {
            $this->sourceConnection->statement("create table `{$this->source}`.`{$table}` like `{$target}`.`{$table}`");
        }

        config(['dbsync.contentSource' => $this->source]);

        DB::table('global_set_variables')->delete();

        $this->sourceConnection->table("{$this->source}.global_set_variables")
            ->insert(globalSetRow('from_production', '{"source":"production"}', '2026-01-02 00:00:00'));
    });

    afterEach(function (): void {
        // The test's open transaction holds metadata locks that would block the drop.
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        $this->sourceConnection->statement("drop database `{$this->source}`");
        DB::purge('staging_content_source');
    });

    it('replaces staging rows with the production rows', function (): void {
        DB::table('global_set_variables')->insert(globalSetRow('stale', '{}', '2026-01-01 00:00:00'));

        $this->artisan('staging:sync-content')->assertSuccessful();

        expect(DB::table('global_set_variables')->pluck('handle')->all())->toBe(['from_production']);
    });

    it('leaves tables outside the allowlist alone', function (): void {
        $this->sourceConnection->table("{$this->source}.users")->insert(
            User::factory()->make(['email' => 'member@example.com'])->makeVisible(['password'])->getAttributes()
        );

        $this->artisan('staging:sync-content')->assertSuccessful();

        expect(DB::table('users')->where('email', 'member@example.com')->exists())->toBeFalse();
    });

    it('skips a table that has not changed', function (): void {
        $this->artisan('staging:sync-content')->assertSuccessful();

        DB::table('global_set_variables')->update(['data' => '{"edited":"on staging"}']);

        $this->artisan('staging:sync-content')->expectsOutputToContain('Skipping `global_set_variables`')->assertSuccessful();

        expect(DB::table('global_set_variables')->value('data'))->toContain('on staging');
    });

    it('copies a table whose latest update differs', function (): void {
        $this->artisan('staging:sync-content')->assertSuccessful();

        DB::table('global_set_variables')->update(['data' => '{"edited":"on staging"}', 'updated_at' => '2026-03-01 00:00:00']);

        $this->artisan('staging:sync-content')->assertSuccessful();

        expect(DB::table('global_set_variables')->value('data'))->toContain('production');
    });

    it('copies an unchanged table when forced', function (): void {
        $this->artisan('staging:sync-content')->assertSuccessful();

        DB::table('global_set_variables')->update(['data' => '{"edited":"on staging"}']);

        $this->artisan('staging:sync-content', ['--force' => true])->assertSuccessful();

        expect(DB::table('global_set_variables')->value('data'))->toContain('production');
    });

    it('ignores columns that only exist in production', function (): void {
        $this->sourceConnection->statement("alter table `{$this->source}`.`global_set_variables` add column `production_only` varchar(10) null");

        $this->artisan('staging:sync-content')->assertSuccessful();

        expect(DB::table('global_set_variables')->pluck('handle')->all())->toBe(['from_production']);
    });
});

it('refuses to run outside staging', function (string $environment): void {
    config(['app.env' => $environment, 'dbsync.contentSource' => 'somewhere_else']);

    $this->artisan('staging:sync-content')
        ->expectsOutputToContain('only be synced into the staging environment')
        ->assertFailed();
})->with(['production', 'local']);

it('refuses to copy a schema onto itself', function (): void {
    config(['app.env' => 'staging', 'dbsync.contentSource' => DB::getDatabaseName()]);

    DB::table('global_set_variables')->insert(globalSetRow('kept', '{}', '2026-01-01 00:00:00'));

    $this->artisan('staging:sync-content', ['--force' => true])->assertFailed();

    expect(DB::table('global_set_variables')->where('handle', 'kept')->exists())->toBeTrue();
});

it('only allows content tables to be copied', function (): void {
    expect(config('dbsync.contentTables'))->toBe([
        'entries',
        'taxonomy_terms',
        'trees',
        'global_set_variables',
        'assets_meta',
        'redirects',
    ]);
});

it('runs the sync hourly on staging only', function (): void {
    $event = scheduledEvent('staging:sync-content');

    expect($event->environments)->toBe(['staging'])
        ->and($event->expression)->toBe('0 * * * *');
});

it('keeps tasks with outside effects off staging', function (string $name): void {
    expect(scheduledEvent($name)->environments)->toBe(['production']);
})->with([
    'fire check-in emails' => fn () => app(SendFiresCheckinsEmailCommand::class)->getName(),
    'donor id updates' => fn () => app(UpdateDonorIdCommand::class)->getName(),
    'activity log archive' => fn () => app(ArchiveActivityLogCommand::class)->getName(),
    'monthly report' => GenerateAndEmailReport::class,
]);
