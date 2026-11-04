<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\ActivityLog\ArchiveActivityLog;

class ArchiveActivityLogCommand extends Command
{
    protected $signature = 'activity-log:archive
                            {--days= : Archive activity older than this many days (defaults to activitylog.delete_records_older_than_days)}
                            {--disk= : Disk to store the archive files on (defaults to the default disk)}
                            {--chunk=5000 : Rows per read and delete batch}';

    protected $description = 'Archive old activity log records to gzipped JSON files and delete them from the database';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('activitylog.delete_records_older_than_days'));
        $disk = $this->option('disk') ?? config('filesystems.default');
        $before = now()->subDays($days)->startOfDay();

        $archived = ArchiveActivityLog::run(
            $before,
            $disk,
            (int) $this->option('chunk'),
            function (string $month, int $firstId, int $lastId): void {
                $this->info("Archiving {$month} (ids {$firstId} to {$lastId})...");
            },
        );

        $this->comment("Archived and deleted {$archived} activity log record(s) created before {$before->toDateString()} to the `{$disk}` disk.");

        return self::SUCCESS;
    }
}
