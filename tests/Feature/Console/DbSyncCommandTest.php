<?php

use Illuminate\Console\OutputStyle;
use App\Console\Commands\DbSyncCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

it('builds structure and data dump filenames next to target file', function () {
    $command = new DbSyncCommand;

    $method = new ReflectionMethod($command, 'buildSiblingFileName');
    $method->setAccessible(true);

    $structure = $method->invoke($command, '/tmp/file.sql', 'structure_');
    $data = $method->invoke($command, '/tmp/file.sql', 'data_');

    expect($structure)->toBe('/tmp/structure_file.sql');
    expect($data)->toBe('/tmp/data_file.sql');
});

function dbSyncConfig(array $overrides = []): array
{
    return array_merge([
        'useSsh' => false,
        'sshUsername' => 'forge',
        'sshPort' => '22',
        'host' => 'db.example.com',
        'mysqlHostName' => '127.0.0.1',
        'username' => 'forge',
        'database' => 'production',
        'port' => '3306',
        'password' => 'pa$s w;rd\'"`x',
        'mysqldumpSkipTzUtc' => '',
        'sslMode' => '',
        'sslCa' => '',
        'localUsername' => 'cloud',
        'localPassword' => 'pa$s w;rd\'"`x',
        'localHostname' => 'cloud.example.com',
        'localPort' => '3306',
        'localDatabase' => 'main',
        'localMysqlPath' => fakeMysqlBinary(),
        'localMysqldumpPath' => fakeMysqlBinary(),
        'fileName' => tempnam(sys_get_temp_dir(), 'dbsync_test_'),
    ], $overrides);
}

function fakeMysqlBinary(string $script = 'printf \'%s|\' "$@"'): string
{
    $directory = sys_get_temp_dir() . '/dbsync test ' . uniqid();
    mkdir($directory);

    $path = "$directory/mysql";
    file_put_contents($path, "#!/bin/sh\n$script\n");
    chmod($path, 0755);

    return $path;
}

function invokeDbSyncMethod(string $name, mixed ...$arguments): mixed
{
    $command = new DbSyncCommand;
    $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));

    return (new ReflectionMethod($command, $name))->invoke($command, ...$arguments);
}

it('passes a password with shell metacharacters to mysql as a single argument', function () {
    $config = dbSyncConfig();

    exec(invokeDbSyncMethod('buildImportCommand', $config), $output);

    expect(implode('', $output))->toContain('|-ppa$s w;rd\'"`x|');
});

it('omits the password option when the target has no password', function () {
    $config = dbSyncConfig(['localPassword' => '']);

    exec(invokeDbSyncMethod('buildImportCommand', $config), $output);

    expect(implode('', $output))->toBe('--max-allowed-packet=1G|-ucloud|-hcloud.example.com|-P3306|main|');
});

it('passes a password with shell metacharacters to a local mysqldump as a single argument', function () {
    $config = dbSyncConfig();
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    exec(invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile));

    expect(file_get_contents($outputFile))->toContain('|--password=pa$s w;rd\'"`x|');
});

it('runs a local mysqldump binary whose path contains a space', function () {
    $config = dbSyncConfig();
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    expect($config['localMysqldumpPath'])->toContain(' ');

    exec(invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile), result_code: $result);

    expect($result)->toBe(0);
    expect(file_get_contents($outputFile))->toStartWith('--single-transaction|');
});

it('requires the configured ssl mode on a local mysqldump', function () {
    $config = dbSyncConfig(['sslMode' => '--ssl-mode=' . escapeshellarg('REQUIRED')]);
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    exec(invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile));

    expect(file_get_contents($outputFile))->toContain('|--ssl-mode=REQUIRED|');
});

it('verifies the server certificate against the configured ca bundle', function () {
    $config = dbSyncConfig([
        'sslMode' => '--ssl-mode=' . escapeshellarg('VERIFY_IDENTITY'),
        'sslCa' => '--ssl-ca=' . escapeshellarg('/etc/ssl/cert.pem'),
    ]);
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    exec(invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile));

    expect(file_get_contents($outputFile))->toContain('|--ssl-mode=VERIFY_IDENTITY|--ssl-ca=/etc/ssl/cert.pem|');
});

it('raises the mysqldump packet limit so large rows export', function () {
    $config = dbSyncConfig();
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    exec(invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile));

    expect(file_get_contents($outputFile))->toContain('|--max-allowed-packet=1G|');
});

it('keeps the password quoted for the remote shell when exporting over ssh', function () {
    $config = dbSyncConfig(['useSsh' => true]);
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    $command = invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile);

    expect($command)->toStartWith('ssh forge@db.example.com -p22 ');

    exec(str_replace('ssh forge@db.example.com -p22 ', "printf '%s' ", $command));
    $remoteCommand = file_get_contents($outputFile);

    exec(str_replace('mysqldump ', "printf '%s|' ", $remoteCommand), $remoteArguments);

    expect(implode('', $remoteArguments))->toContain('|--password=pa$s w;rd\'"`x|');
});

it('runs a mysql binary whose path contains a space', function () {
    $config = dbSyncConfig();

    expect($config['localMysqlPath'])->toContain(' ');

    exec(invokeDbSyncMethod('buildImportCommand', $config), $output, $result);

    expect($result)->toBe(0);
    expect(implode('', $output))->toStartWith('--max-allowed-packet=1G|-ucloud|');
});

it('reports failure when the import fails', function () {
    $config = dbSyncConfig(['localMysqlPath' => fakeMysqlBinary('exit 1')]);

    expect(invokeDbSyncMethod('importDatabase', $config))->toBe(DbSyncCommand::FAILURE);
});

it('reports failure when the mysql binary does not exist', function () {
    $config = dbSyncConfig(['localMysqlPath' => '/nonexistent/mysql']);

    expect(invokeDbSyncMethod('importDatabase', $config))->toBe(DbSyncCommand::FAILURE);
});

it('exits with a failure code outside the allowed environments', function () {
    config(['dbsync.environments' => ['local']]);

    $this->artisan('db:production-sync --test')->assertFailed();
});

it('defaults the import and cleanup flags to booleans', function () {
    $config = require base_path('config/dbsync.php');

    expect($config['importSqlFile'])->toBeTrue();
    expect($config['removeFileAfterImport'])->toBeTrue();
});

it('exports the structure once and each table\'s data on its own connection', function () {
    $structure = 'CREATE TABLE `entries` (id int);\nCREATE TABLE `activity_log` (id int);\nCREATE TABLE `users` (id int);';

    $config = dbSyncConfig([
        'localMysqldumpPath' => fakeMysqlBinary(<<<SH
            case "\$*" in
                *--no-data*) printf '{$structure}\n' ;;
                *) while [ "\$1" != "--no-create-info" ]; do shift; done; printf -- '-- data for %s\n' "\$2" ;;
            esac
            SH),
    ]);

    $result = invokeDbSyncMethod('exportDatabase', $config, [], ['activity_log']);

    expect($result)->toBe(DbSyncCommand::SUCCESS);
    expect(file_get_contents($config['fileName']))->toBe(
        "CREATE TABLE `entries` (id int);\nCREATE TABLE `activity_log` (id int);\nCREATE TABLE `users` (id int);\n"
        . "-- data for entries\n-- data for users\n"
    );
});

it('stops and cleans up when a table\'s data export fails', function () {
    $config = dbSyncConfig([
        'localMysqldumpPath' => fakeMysqlBinary(<<<'SH'
            case "$*" in
                *--no-data*) printf 'CREATE TABLE `entries` (id int);\n' ;;
                *) exit 2 ;;
            esac
            SH),
    ]);

    $result = invokeDbSyncMethod('exportDatabase', $config, [], []);

    expect($result)->toBe(DbSyncCommand::FAILURE);
    expect(file_exists(dirname($config['fileName']) . '/structure_' . basename($config['fileName'])))->toBeFalse();
    expect(file_exists(dirname($config['fileName']) . '/data_' . basename($config['fileName'])))->toBeFalse();
});

it('reads table names from a structure dump, including escaped backticks', function () {
    $file = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    file_put_contents($file, "-- comment\nCREATE TABLE `entries` (\n  `id` int\n);\nCREATE TABLE `odd``name` (id int);\n/*!50001 CREATE VIEW `v` AS SELECT 1 */;\n");

    expect(invokeDbSyncMethod('tablesInStructureDump', $file))->toBe(['entries', 'odd`name']);
});

it('keeps table order across parallel batches even when tables finish out of order', function () {
    $tables = array_map(fn (int $number) => "table_{$number}", range(1, 10));
    $structure = implode('\n', array_map(fn (string $table) => "CREATE TABLE `{$table}` (id int);", $tables));

    $config = dbSyncConfig([
        'localMysqldumpPath' => fakeMysqlBinary(<<<SH
            case "\$*" in
                *--no-data*) printf '{$structure}\n' ;;
                *) while [ "\$1" != "--no-create-info" ]; do shift; done
                   [ "\$2" = "table_1" ] && sleep 0.3
                   printf -- '-- data for %s\n' "\$2" ;;
            esac
            SH),
    ]);

    expect(invokeDbSyncMethod('exportDatabase', $config, [], []))->toBe(DbSyncCommand::SUCCESS);

    $dataLines = array_values(array_filter(
        explode("\n", file_get_contents($config['fileName'])),
        fn (string $line) => str_starts_with($line, '-- data for ')
    ));

    expect($dataLines)->toBe(array_map(fn (string $table) => "-- data for {$table}", $tables));
});
