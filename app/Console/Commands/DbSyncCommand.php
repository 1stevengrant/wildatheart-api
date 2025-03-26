<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbSyncCommand extends Command
{
    protected $signature = 'db:production-sync {--T|test} {--F|filename=}';

    protected $description = 'Sync production database with local';

    public function handle(): int
    {
        $inTest = $this->option('test');

        if (! in_array(config('app.env'), config('dbsync.environments'))) {
            $this->error('DB sync will only run on local and staging environments');

            return self::SUCCESS;
        }

        // Get configuration values
        $config = $this->getConfiguration();

        if (empty($config['host']) || empty($config['username']) || empty($config['database'])) {
            $this->error('DB credentials not set, have you published the config and set ENV variables?');

            return self::SUCCESS;
        }

        if ($inTest === false) {
            $totalSteps = 2;
            $progressBar = $this->output->createProgressBar($totalSteps);

            // Prepare tables for export
            $ignoreTables = explode(',', $config['ignore']);
            $excludeDataTables = ! empty($config['excludeData']) ? explode(',', $config['excludeData']) : [];

            // Export and import the database
            $exportResult = $this->exportDatabase($config, $ignoreTables, $excludeDataTables);
            if ($exportResult !== self::SUCCESS) {
                return $exportResult;
            }

            $progressBar->advance();

            $importResult = $this->importDatabase($config);
            if ($importResult !== self::SUCCESS) {
                return $importResult;
            }

            $progressBar->advance();
            $progressBar->finish();

            // Clean up
            if ($config['removeFileAfterImport'] === true) {
                unlink($config['fileName']);
            }
        }

        $this->newLine();
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
            'useSsh' => config('dbsync.useSsh'),
            'sshUsername' => config('dbsync.sshUsername'),
            'sshPort' => config('dbsync.sshPort'),
            'host' => config('dbsync.host'),
            'mysqlHostName' => config('dbsync.mysqlHostName'),
            'username' => config('dbsync.username'),
            'database' => config('dbsync.database'),
            'port' => config('dbsync.port'),
            'password' => config('dbsync.password'),
            'ignore' => config('dbsync.ignore'),
            'excludeData' => config('dbsync.excludeData'),
            'mysqldumpSkipTzUtc' => config('dbsync.mysqldumpSkipTzUtc') ? '--skip-tz-utc' : '',

            // Local settings
            'localUsername' => config("database.connections.{$defaultConnection}.username"),
            'localPassword' => config("database.connections.{$defaultConnection}.password"),
            'localHostname' => config("database.connections.{$defaultConnection}.host"),
            'localPort' => config("database.connections.{$defaultConnection}.port"),
            'localDatabase' => config("database.connections.{$defaultConnection}.database"),
            'localMysqlPath' => config('dbsync.localMysqlPath'),

            // File settings
            'importSqlFile' => config('dbsync.importSqlFile'),
            'removeFileAfterImport' => config('dbsync.removeFileAfterImport'),
            'fileName' => $this->option('filename') ?? config('dbsync.defaultFileName'),
        ];
    }

    /**
     * Export the database
     */
    private function exportDatabase(array $config, array $ignoreTables, array $excludeDataTables): int
    {
        // Create ignore tables string
        $ignoreString = $this->buildIgnoreString($config['database'], $ignoreTables);

        // Handle structure-only tables
        if (! empty($excludeDataTables)) {
            return $this->exportWithStructureOnlyTables($config, $ignoreString, $excludeDataTables);
        }

        // Simple export without structure-only tables
        return $this->executeExport($config, $ignoreString, $config['fileName']);
    }

    /**
     * Build the ignore tables string
     */
    private function buildIgnoreString(string $database, array $tables): string
    {
        $ignoreString = '';
        foreach ($tables as $name) {
            if (mb_trim($name) !== '') {
                $ignoreString .= " --ignore-table=$database.$name";
            }
        }

        return $ignoreString;
    }

    /**
     * Export database with structure-only tables
     */
    private function exportWithStructureOnlyTables(array $config, string $ignoreString, array $excludeDataTables): int
    {
        // First export only structure for tables where we want structure but no data
        $structureOnlyFile = 'structure_' . $config['fileName'];
        $structureOnlyTables = implode(' ', array_map('trim', $excludeDataTables));

        $structureResult = $this->executeExport(
            $config,
            '', // No ignore string for structure export
            $structureOnlyFile,
            '--no-data ' . $structureOnlyTables
        );

        if ($structureResult !== self::SUCCESS) {
            $this->error('Structure export failed');

            return self::FAILURE;
        }

        // Then export data for all tables excluding both the ignored tables and structure-only tables
        $dataIgnoreString = $ignoreString;
        foreach ($excludeDataTables as $name) {
            if (mb_trim($name) !== '') {
                $dataIgnoreString .= " --ignore-table={$config['database']}.$name";
            }
        }

        $dataOnlyFile = 'data_' . $config['fileName'];
        $dataResult = $this->executeExport($config, $dataIgnoreString, $dataOnlyFile);

        if ($dataResult !== self::SUCCESS) {
            $this->error('Data export failed');

            return self::FAILURE;
        }

        // Combine the files
        exec("cat $structureOnlyFile $dataOnlyFile > {$config['fileName']}");
        unlink($structureOnlyFile);
        unlink($dataOnlyFile);

        return self::SUCCESS;
    }

    /**
     * Execute mysqldump export
     */
    private function executeExport(array $config, string $ignoreString, string $outputFile, string $additionalOptions = ''): int
    {
        $commonOptions = "--single-transaction --set-gtid-purged=OFF --port={$config['port']} --host={$config['mysqlHostName']} --user={$config['username']} --password={$config['password']} {$config['database']} $ignoreString $additionalOptions";

        if ($config['useSsh'] === true) {
            $command = "ssh {$config['sshUsername']}@{$config['host']} -p{$config['sshPort']} mysqldump $commonOptions > $outputFile 2>/dev/null";
        } else {
            $command = "mysqldump $commonOptions {$config['mysqldumpSkipTzUtc']} --column-statistics=0 > $outputFile 2>/dev/null";
        }

        $result = 0;
        exec($command, $output, $result);

        return $result === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Import the database
     */
    private function importDatabase(array $config): int
    {
        $command = $config['localPassword']
            ? "{$config['localMysqlPath']} -u{$config['localUsername']} -h{$config['localHostname']} -p{$config['localPassword']} -P{$config['localPort']} {$config['localDatabase']} < {$config['fileName']} 2>/dev/null"
            : "{$config['localMysqlPath']} -u{$config['localUsername']} -h{$config['localHostname']} -P{$config['localPort']} {$config['localDatabase']} < {$config['fileName']} 2>/dev/null";

        $result = 0;
        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('Import failed');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
