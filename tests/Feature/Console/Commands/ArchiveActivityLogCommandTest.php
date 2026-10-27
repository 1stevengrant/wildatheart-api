<?php

use App\Models\Activity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Actions\ActivityLog\ArchiveActivityLog;

function logActivityAt(string $createdAt, string $description = 'Something happened'): Activity
{
    $activity = activity('auth')->withProperties(['ip' => '127.0.0.1'])->log($description);
    $activity->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

    return $activity;
}

/**
 * @return array<int, array<string, mixed>>
 */
function archivedRows(string $path): array
{
    $lines = array_filter(explode("\n", gzdecode(Storage::disk('archive')->get($path))));

    return array_map(fn (string $line): array => json_decode($line, true), array_values($lines));
}

beforeEach(function () {
    Storage::fake('archive');
    Carbon::setTestNow('2026-10-28 12:00:00');
    Activity::query()->delete();
});

it('archives old activity into monthly files and deletes it', function () {
    logActivityAt('2026-06-03 10:00:00', 'June one');
    logActivityAt('2026-06-20 10:00:00', 'June two');
    $july = logActivityAt('2026-07-15 10:00:00', 'July one');
    $recent = logActivityAt('2026-10-01 10:00:00', 'Recent');

    $this->artisan('activity-log:archive', ['--days' => 90, '--disk' => 'archive'])
        ->expectsOutputToContain('Archiving 2026-06')
        ->expectsOutputToContain('Archived and deleted 3 activity log record(s)')
        ->assertSuccessful();

    $juneFiles = Storage::disk('archive')->files('activity-log-archive/2026/2026-06');
    $julyFiles = Storage::disk('archive')->files('activity-log-archive/2026/2026-07');

    expect($juneFiles)->toHaveCount(1)
        ->and($julyFiles)->toHaveCount(1)
        ->and(archivedRows($juneFiles[0]))->sequence(
            fn ($row) => $row->description->toBe('June one')->properties->toBe(['ip' => '127.0.0.1']),
            fn ($row) => $row->description->toBe('June two'),
        )
        ->and(archivedRows($julyFiles[0])[0]['id'])->toBe($july->id)
        ->and(Activity::query()->pluck('id')->all())->toBe([$recent->id]);
});

it('does nothing when no activity is old enough', function () {
    logActivityAt('2026-10-01 10:00:00');

    $this->artisan('activity-log:archive', ['--days' => 90, '--disk' => 'archive'])
        ->expectsOutputToContain('Archived and deleted 0 activity log record(s)')
        ->assertSuccessful();

    expect(Storage::disk('archive')->allFiles())->toBeEmpty()
        ->and(Activity::query()->count())->toBe(1);
});

it('keeps the rows when the archive cannot be stored', function () {
    logActivityAt('2026-06-03 10:00:00');

    $disk = Mockery::mock(Storage::disk('archive'))->makePartial();
    $disk->shouldReceive('writeStream')->andReturnFalse();
    Storage::set('archive', $disk);

    expect(fn () => ArchiveActivityLog::run(now()->subDays(90), 'archive'))
        ->toThrow(RuntimeException::class, 'Could not store the activity log archive');

    expect(Activity::query()->count())->toBe(1);
});
