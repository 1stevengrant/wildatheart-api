<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupTestDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cleanup-databases
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up test databases created by parallel testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseDatabase = config('database.connections.mysql.database');

        // Pattern to match test databases
        // Since our base is wahlocal_test, parallel tests create wahlocal_test_test_1, etc.
        $pattern = $baseDatabase . '_test_%';

        // Get list of databases matching the pattern
        // Use a safer approach with SHOW DATABASES and filter in PHP
        $allDatabases = DB::select('SHOW DATABASES');
        $databases = array_filter($allDatabases, function ($db) use ($baseDatabase) {
            $dbName = array_values((array) $db)[0];

            // Match pattern: baseDatabase_test_[number]
            return preg_match('/^' . preg_quote($baseDatabase, '/') . '_test_\d+$/', $dbName);
        });

        if (empty($databases)) {
            $this->info('No test databases found to clean up.');

            return Command::SUCCESS;
        }

        $this->info('Found ' . count($databases) . ' test database(s) to clean up:');

        foreach ($databases as $db) {
            $dbName = array_values((array) $db)[0];
            $this->line('  - ' . $dbName);
        }

        if (! $this->option('force')) {
            if (! $this->confirm('Do you want to drop these databases?')) {
                $this->info('Operation cancelled.');

                return Command::SUCCESS;
            }
        }

        foreach ($databases as $db) {
            $dbName = array_values((array) $db)[0];
            try {
                Schema::dropDatabaseIfExists($dbName);
                $this->info('Dropped: ' . $dbName);
            } catch (\Exception $e) {
                $this->error('Failed to drop ' . $dbName . ': ' . $e->getMessage());
            }
        }

        $this->info('Cleanup completed!');

        return Command::SUCCESS;
    }
}
