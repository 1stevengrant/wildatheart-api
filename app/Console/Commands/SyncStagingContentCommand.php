<?php

namespace App\Console\Commands;

use stdClass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncStagingContentCommand extends Command
{
    protected $signature = 'staging:sync-content {--force : Copy every table, even if it looks unchanged}';

    protected $description = 'Copy CMS content from the production schema into the staging schema';

    public function handle(): int
    {
        $source = config('dbsync.contentSource');
        $target = DB::getDatabaseName();

        if (config('app.env') !== 'staging') {
            $this->error('Content can only be synced into the staging environment.');

            return self::FAILURE;
        }

        if ($source === $target) {
            $this->error("The source and target are both `{$target}`.");

            return self::FAILURE;
        }

        $copied = 0;

        foreach (config('dbsync.contentTables') as $table) {
            if (! $this->option('force') && $this->isUnchanged($table, $source, $target)) {
                $this->line("Skipping `{$table}`, no changes.");

                continue;
            }

            $this->info("Copying `{$table}`...");
            $this->copyTable($table, $source, $target);
            $copied++;
        }

        $this->comment("Copied {$copied} tables from `{$source}` to `{$target}`.");

        return self::SUCCESS;
    }

    private function isUnchanged(string $table, string $source, string $target): bool
    {
        return $this->fingerprint($table, $source) == $this->fingerprint($table, $target);
    }

    private function fingerprint(string $table, string $database): stdClass
    {
        return DB::selectOne("select count(*) as row_count, max(updated_at) as last_updated from `{$database}`.`{$table}`");
    }

    private function copyTable(string $table, string $source, string $target): void
    {
        $columns = collect(Schema::getColumnListing("{$source}.{$table}"))
            ->intersect(Schema::getColumnListing("{$target}.{$table}"))
            ->map(fn (string $column): string => "`{$column}`")
            ->implode(', ');

        DB::transaction(function () use ($table, $source, $target, $columns): void {
            DB::delete("delete from `{$target}`.`{$table}`");
            DB::insert("insert into `{$target}`.`{$table}` ({$columns}) select {$columns} from `{$source}`.`{$table}`");
        });
    }
}
