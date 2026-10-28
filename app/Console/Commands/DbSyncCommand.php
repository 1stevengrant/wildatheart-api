<?php

namespace App\Console\Commands;

use Illuminate\Process\Pool;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DbSyncCommand extends Command
{
    private const PARALLEL_TABLE_EXPORTS = 8;

    protected $signature = 'db:production-sync {--T|test} {--F|filename=}';

    protected $description = 'Sync production database with local';

    public function handle(): int
    {
        $inTest = $this->option('test');

        if (! in_array(config('app.env'), config('dbsync.environments'))) {
            $this->error('DB sync will only run on local and staging environments');

            return self::FAILURE;
        }

        // Get configuration values
        $config = $this->getConfiguration();

        if (empty($config['host']) || empty($config['username']) || empty($config['database'])) {
            $this->error('DB credentials not set, have you published the config and set ENV variables?');

            return self::FAILURE;
        }

        if ($inTest === false) {
            // Prepare tables for export
            $ignoreTables = explode(',', $config['ignore']);
            $excludeDataTables = ! empty($config['excludeData']) ? explode(',', $config['excludeData']) : [];

            // Export and import the database
            $exportResult = $this->exportDatabase($config, $ignoreTables, $excludeDataTables);
            if ($exportResult !== self::SUCCESS) {
                return $exportResult;
            }

            if ($config['importSqlFile'] === false) {
                $this->info("Dump written to {$config['fileName']}, import skipped");

                return self::SUCCESS;
            }

            $this->info("Importing into {$config['localDatabase']}...");

            $importResult = $this->importDatabase($config);
            if ($importResult !== self::SUCCESS) {
                return $importResult;
            }

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
            'sslMode' => config('dbsync.sslMode') ? '--ssl-mode=' . escapeshellarg(config('dbsync.sslMode')) : '',
            'sslCa' => config('dbsync.sslCa') ? '--ssl-ca=' . escapeshellarg(config('dbsync.sslCa')) : '',

            // Local settings
            'localUsername' => config("database.connections.{$defaultConnection}.username"),
            'localPassword' => config("database.connections.{$defaultConnection}.password"),
            'localHostname' => config("database.connections.{$defaultConnection}.host"),
            'localPort' => config("database.connections.{$defaultConnection}.port"),
            'localDatabase' => config("database.connections.{$defaultConnection}.database"),
            'localMysqlPath' => config('dbsync.localMysqlPath'),
            'localMysqldumpPath' => config('dbsync.localMysqldumpPath'),

            // File settings
            'importSqlFile' => config('dbsync.importSqlFile'),
            'removeFileAfterImport' => config('dbsync.removeFileAfterImport'),
            'fileName' => $this->resolveFileName(),
        ];
    }

    /**
     * Resolve the SQL dump filename to an absolute path.
     */
    private function resolveFileName(): string
    {
        $fileName = $this->option('filename') ?? config('dbsync.defaultFileName');

        if (! str_starts_with($fileName, '/')) {
            $fileName = sys_get_temp_dir() . '/' . $fileName;
        }

        return $fileName;
    }

    /**
     * Export the structure of every table in one dump, then each table's data on its own connection.
     *
     * Laravel Cloud's ProxySQL returns a malformed packet partway through a single dump of every
     * table, while the same tables dump cleanly one connection at a time.
     */
    private function exportDatabase(array $config, array $ignoreTables, array $excludeDataTables): int
    {
        $structureFile = $this->buildSiblingFileName($config['fileName'], 'structure_');
        $dataFile = $this->buildSiblingFileName($config['fileName'], 'data_');

        $this->info('Exporting table structure...');

        $ignoreString = $this->buildIgnoreString($config['database'], $ignoreTables);

        if ($this->executeExport($config, $ignoreString, $structureFile, '--no-data') !== self::SUCCESS) {
            $this->error('Structure export failed');
            @unlink($structureFile);

            return self::FAILURE;
        }

        $excludeDataTables = array_map('trim', $excludeDataTables);

        $tables = array_values(array_filter(
            $this->tablesInStructureDump($structureFile),
            fn (string $table) => ! in_array($table, $excludeDataTables, true)
        ));

        file_put_contents($dataFile, '');

        foreach (array_chunk($tables, self::PARALLEL_TABLE_EXPORTS) as $batch) {
            $this->line('Exporting data for ' . implode(', ', $batch) . '...');

            if ($this->exportTableData($config, $batch, $dataFile) !== self::SUCCESS) {
                @unlink($structureFile);
                @unlink($dataFile);

                return self::FAILURE;
            }
        }

        file_put_contents($config['fileName'], '');
        $this->appendFile($structureFile, $config['fileName']);
        $this->appendFile($dataFile, $config['fileName']);

        unlink($structureFile);
        unlink($dataFile);

        $this->info('Exported ' . count($tables) . ' tables');

        return self::SUCCESS;
    }

    /**
     * Dump a batch of tables' data in parallel, then append each to the data file in batch order
     *
     * @param  array<int, string>  $tables
     */
    private function exportTableData(array $config, array $tables, string $dataFile): int
    {
        $files = array_map(fn (string $table) => [
            'output' => tempnam(sys_get_temp_dir(), 'dbsync_table_'),
            'error' => tempnam(sys_get_temp_dir(), 'dbsync_error_'),
        ], $tables);

        $results = Process::concurrently(function (Pool $pool) use ($config, $tables, $files) {
            foreach ($tables as $index => $table) {
                $pool->as((string) $index)->forever()->command($this->buildExportCommand(
                    $config,
                    '',
                    $files[$index]['output'],
                    $files[$index]['error'],
                    '--no-create-info ' . escapeshellarg($table)
                ));
            }
        });

        $failed = false;

        foreach ($tables as $index => $table) {
            if ($results[(string) $index]->failed()) {
                $failed = true;
                $this->error("Data export failed for `{$table}`: " . file_get_contents($files[$index]['error']));
            }

            if (! $failed) {
                $this->appendFile($files[$index]['output'], $dataFile);
            }

            @unlink($files[$index]['output']);
            @unlink($files[$index]['error']);
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Read the table names from a mysqldump structure dump, in dump order
     *
     * @return array<int, string>
     */
    private function tablesInStructureDump(string $structureFile): array
    {
        $tables = [];
        $handle = fopen($structureFile, 'r');

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^CREATE TABLE `((?:[^`]|``)+)`/', $line, $matches) === 1) {
                $tables[] = str_replace('``', '`', $matches[1]);
            }
        }

        fclose($handle);

        return $tables;
    }

    /**
     * Append one file to another without loading either into memory
     */
    private function appendFile(string $source, string $destination): void
    {
        $from = fopen($source, 'r');
        $to = fopen($destination, 'a');

        stream_copy_to_stream($from, $to);

        fclose($from);
        fclose($to);
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
     * Build a sibling filename next to $fileName with a prefix.
     *
     * Example: /tmp/file.sql + structure_ => /tmp/structure_file.sql
     */
    private function buildSiblingFileName(string $fileName, string $prefix): string
    {
        $directory = dirname($fileName);
        $baseName = basename($fileName);

        if ($baseName === '' || $baseName === '.' || $baseName === DIRECTORY_SEPARATOR) {
            $baseName = 'file.sql';
        }

        return mb_rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $prefix . $baseName;
    }

    /**
     * Execute mysqldump export
     */
    private function executeExport(array $config, string $ignoreString, string $outputFile, string $additionalOptions = ''): int
    {
        $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_');
        $command = $this->buildExportCommand($config, $ignoreString, $outputFile, $errorFile, $additionalOptions);

        $result = 0;
        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('Export failed: ' . file_get_contents($errorFile));
        }

        @unlink($errorFile);

        return $result === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Build the mysqldump command, quoting the remote command again when it runs over SSH
     */
    private function buildExportCommand(array $config, string $ignoreString, string $outputFile, string $errorFile, string $additionalOptions = ''): string
    {
        $password = escapeshellarg($config['password']);
        $commonOptions = "--single-transaction --max-allowed-packet=1G --no-tablespaces --set-gtid-purged=OFF --port={$config['port']} --host={$config['mysqlHostName']} --user={$config['username']} --password=$password {$config['database']} $ignoreString $additionalOptions";

        $outputFileArg = escapeshellarg($outputFile);
        $errorFileArg = escapeshellarg($errorFile);

        if ($config['useSsh'] === true) {
            $remoteCommand = escapeshellarg("mysqldump $commonOptions");

            return "ssh {$config['sshUsername']}@{$config['host']} -p{$config['sshPort']} $remoteCommand > $outputFileArg 2>$errorFileArg";
        }

        $mysqldumpPathArg = escapeshellarg($config['localMysqldumpPath']);

        return "$mysqldumpPathArg $commonOptions {$config['mysqldumpSkipTzUtc']} {$config['sslMode']} {$config['sslCa']} --column-statistics=0 > $outputFileArg 2>$errorFileArg";
    }

    /**
     * Import the database
     */
    private function importDatabase(array $config): int
    {
        $command = $this->buildImportCommand($config);

        $result = 0;
        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('Import failed: ' . implode("\n", $output));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Build the mysql import command
     */
    private function buildImportCommand(array $config): string
    {
        $mysqlPathArg = escapeshellarg($config['localMysqlPath']);
        $fileNameArg = escapeshellarg($config['fileName']);
        $passwordOption = $config['localPassword'] ? ' -p' . escapeshellarg($config['localPassword']) : '';

        return "$mysqlPathArg --max-allowed-packet=1G -u{$config['localUsername']} -h{$config['localHostname']}$passwordOption -P{$config['localPort']} {$config['localDatabase']} < $fileNameArg 2>&1";
    }
}
