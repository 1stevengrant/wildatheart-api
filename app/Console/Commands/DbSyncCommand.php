<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class DbSyncCommand extends Command
{
    protected $signature = 'db:production-sync {--T|test} {--D|directory=}';

    protected $description = 'Sync production database with local using mydumper and myloader';

    public function handle(): int
    {
        if (! in_array(config('app.env'), config('dbsync.environments'))) {
            $this->error('DB sync will only run on local and staging environments');

            return self::FAILURE;
        }

        $config = $this->getConfiguration();

        if (empty($config['host']) || empty($config['username']) || empty($config['database'])) {
            $this->error('DB credentials not set, have you published the config and set ENV variables?');

            return self::FAILURE;
        }

        if ($this->option('test')) {
            $this->info('DB Synced');

            return self::SUCCESS;
        }

        if ($this->exportDatabase($config) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($config['importDump'] === false) {
            $this->info("Dump written to {$config['directory']}, import skipped");

            return self::SUCCESS;
        }

        if ($this->importDatabase($config) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($config['removeDumpAfterImport'] === true) {
            File::deleteDirectory($config['directory']);
        }

        $this->info('DB Synced');

        return self::SUCCESS;
    }

    /**
     * Get all configuration values
     */
    private function getConfiguration(): array
    {
        $targetConnection = config('dbsync.targetConnection');
        $defaultConnection = config('database.default');
        $defaultConnection = empty($targetConnection) ? $defaultConnection : $targetConnection;

        return [
            // Remote connection settings
            'host' => config('dbsync.host'),
            'port' => config('dbsync.port'),
            'username' => config('dbsync.username'),
            'password' => config('dbsync.password'),
            'database' => config('dbsync.database'),
            'ignore' => $this->tableList(config('dbsync.ignore')),
            'excludeData' => $this->tableList(config('dbsync.excludeData')),
            'skipTzUtc' => (bool) config('dbsync.skipTzUtc'),
            'sslMode' => config('dbsync.sslMode'),
            'sslCa' => config('dbsync.sslCa'),
            'threads' => config('dbsync.threads'),
            'verbosity' => config('dbsync.verbosity'),
            'mydumperPath' => config('dbsync.mydumperPath'),

            // Local settings
            'localUsername' => config("database.connections.{$defaultConnection}.username"),
            'localPassword' => config("database.connections.{$defaultConnection}.password"),
            'localHostname' => config("database.connections.{$defaultConnection}.host"),
            'localPort' => config("database.connections.{$defaultConnection}.port"),
            'localDatabase' => config("database.connections.{$defaultConnection}.database"),
            'myloaderPath' => config('dbsync.myloaderPath'),

            // Dump settings
            'importDump' => config('dbsync.importDump'),
            'removeDumpAfterImport' => config('dbsync.removeDumpAfterImport'),
            'directory' => $this->resolveDirectory(),
        ];
    }

    /**
     * Split a comma separated list of table names
     *
     * @return array<int, string>
     */
    private function tableList(?string $tables): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $tables ?? ''))));
    }

    /**
     * Resolve the dump directory to an absolute path.
     */
    private function resolveDirectory(): string
    {
        $directory = $this->option('directory') ?? config('dbsync.defaultDirectory');

        if (! str_starts_with($directory, '/')) {
            $directory = sys_get_temp_dir() . '/' . $directory;
        }

        return $directory;
    }

    /**
     * Dump the production database in parallel with mydumper
     */
    private function exportDatabase(array $config): int
    {
        $defaultsFile = $this->writeDefaultsFile($this->exportDefaults($config));
        $omitFile = $this->writeOmitFile($config);

        $this->info("Exporting {$config['database']} with {$config['threads']} threads...");

        try {
            $result = Process::forever()->run($this->buildExportCommand($config, $defaultsFile, $omitFile));
        } finally {
            unlink($defaultsFile);

            if ($omitFile !== null) {
                unlink($omitFile);
            }
        }

        if ($result->failed()) {
            $this->error('Export failed: ' . mb_trim($result->errorOutput() ?: $result->output()));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Build the mydumper command, connection settings stay in the defaults file so the password is not visible in the process list
     *
     * @return array<int, string>
     */
    private function buildExportCommand(array $config, string $defaultsFile, ?string $omitFile): array
    {
        return array_values(array_filter([
            $config['mydumperPath'],
            "--defaults-extra-file={$defaultsFile}",
            "--database={$config['database']}",
            "--outputdir={$config['directory']}",
            '--clear',
            "--threads={$config['threads']}",
            '--sync-thread-lock-mode=NO_LOCK',
            '--triggers',
            $omitFile !== null ? "--omit-from-file={$omitFile}" : null,
            $config['skipTzUtc'] ? '--skip-tz-utc' : null,
            "--verbose={$config['verbosity']}",
        ]));
    }

    /**
     * Connection settings for mydumper, plus an empty WHERE for tables that only need their structure
     *
     * @return array<string, array<string, string>>
     */
    private function exportDefaults(array $config): array
    {
        $defaults = [
            'mydumper' => array_filter([
                'host' => $config['host'],
                'port' => (string) $config['port'],
                'user' => $config['username'],
                'password' => $config['password'],
                'ssl-mode' => $config['sslMode'],
                'ca' => $config['sslCa'],
            ], fn (?string $value) => filled($value)),
        ];

        foreach ($config['excludeData'] as $table) {
            $defaults["`{$config['database']}`.`{$table}`"] = ['where' => '1=0'];
        }

        return $defaults;
    }

    /**
     * Write the tables to skip entirely, or return null when there are none
     */
    private function writeOmitFile(array $config): ?string
    {
        if ($config['ignore'] === []) {
            return null;
        }

        $omitFile = tempnam(sys_get_temp_dir(), 'dbsync_omit_');

        file_put_contents($omitFile, implode("\n", array_map(
            fn (string $table) => "{$config['database']}.{$table}",
            $config['ignore']
        )) . "\n");

        return $omitFile;
    }

    /**
     * Load the dump into the local database in parallel with myloader, replacing tables that already exist
     */
    private function importDatabase(array $config): int
    {
        $defaultsFile = $this->writeDefaultsFile([
            'myloader' => array_filter([
                'host' => $config['localHostname'],
                'port' => (string) $config['localPort'],
                'user' => $config['localUsername'],
                'password' => $config['localPassword'],
            ], fn (?string $value) => filled($value)),
        ]);

        $this->info("Importing into {$config['localDatabase']} with {$config['threads']} threads...");

        try {
            $result = Process::forever()->run($this->buildImportCommand($config, $defaultsFile));
        } finally {
            unlink($defaultsFile);
        }

        if ($result->failed()) {
            $this->error('Import failed: ' . mb_trim($result->errorOutput() ?: $result->output()));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Build the myloader command
     *
     * @return array<int, string>
     */
    private function buildImportCommand(array $config, string $defaultsFile): array
    {
        return [
            $config['myloaderPath'],
            "--defaults-extra-file={$defaultsFile}",
            "--directory={$config['directory']}",
            "--database={$config['localDatabase']}",
            '--drop-table=DROP',
            "--threads={$config['threads']}",
            "--verbose={$config['verbosity']}",
        ];
    }

    /**
     * Write a private GKeyFile style defaults file, which mydumper and myloader read their options from
     *
     * @param  array<string, array<string, string>>  $groups
     */
    private function writeDefaultsFile(array $groups): string
    {
        $defaultsFile = tempnam(sys_get_temp_dir(), 'dbsync_defaults_');

        $contents = '';

        foreach ($groups as $group => $values) {
            $contents .= "[{$group}]\n";

            foreach ($values as $key => $value) {
                $contents .= "{$key} = {$this->escapeDefaultsValue($value)}\n";
            }
        }

        file_put_contents($defaultsFile, $contents);

        return $defaultsFile;
    }

    /**
     * Escape a value the way GLib's key file parser expects
     */
    private function escapeDefaultsValue(string $value): string
    {
        $escaped = str_replace(['\\', "\n", "\r", "\t"], ['\\\\', '\\n', '\\r', '\\t'], $value);

        return str_starts_with($escaped, ' ')
            ? '\\s' . mb_substr($escaped, 1)
            : $escaped;
    }
}
