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
        'localUsername' => 'cloud',
        'localPassword' => 'pa$s w;rd\'"`x',
        'localHostname' => 'cloud.example.com',
        'localPort' => '3306',
        'localDatabase' => 'main',
        'localMysqlPath' => fakeMysqlBinary(),
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

    expect(implode('', $output))->toBe('-ucloud|-hcloud.example.com|-P3306|main|');
});

it('passes a password with shell metacharacters to a local mysqldump as a single argument', function () {
    $config = dbSyncConfig();
    $outputFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');
    $errorFile = tempnam(sys_get_temp_dir(), 'dbsync_test_');

    $command = invokeDbSyncMethod('buildExportCommand', $config, '', $outputFile, $errorFile);
    exec(str_replace('mysqldump ', "printf '%s|' ", $command));

    expect(file_get_contents($outputFile))->toContain('|--password=pa$s w;rd\'"`x|');
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
    expect(implode('', $output))->toStartWith('-ucloud|');
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
