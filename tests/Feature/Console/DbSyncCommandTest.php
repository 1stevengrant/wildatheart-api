<?php

use Illuminate\Process\PendingProcess;
use App\Console\Commands\DbSyncCommand;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    config([
        'dbsync.host' => 'db.example.com',
        'dbsync.port' => '3306',
        'dbsync.username' => 'cloud',
        'dbsync.password' => 'pa$s w;rd\'"`x',
        'dbsync.database' => 'production',
        'dbsync.ignore' => '',
        'dbsync.excludeData' => 'activity_log, failed_jobs',
        'dbsync.sslMode' => 'VERIFY_IDENTITY',
        'dbsync.sslCa' => '/etc/ssl/cert.pem',
        'dbsync.threads' => 8,
        'dbsync.verbosity' => 1,
        'dbsync.mydumperPath' => 'mydumper',
        'dbsync.myloaderPath' => 'myloader',
        'dbsync.importDump' => true,
        'dbsync.removeDumpAfterImport' => true,
        'dbsync.defaultDirectory' => 'dbsync_test_' . uniqid(),
    ]);
});

/**
 * Fake both binaries, keeping each command and the files it was given before the command deletes them
 *
 * @return ArrayObject<string, array{command: array<int, string>, files: array<string, string>}>
 */
function fakeDumpTools(int $mydumperExitCode = 0, int $myloaderExitCode = 0): ArrayObject
{
    $runs = new ArrayObject;

    Process::fake(function (PendingProcess $process) use ($runs, $mydumperExitCode, $myloaderExitCode) {
        $binary = basename($process->command[0]);
        $files = [];

        foreach ($process->command as $argument) {
            if (preg_match('/^--(defaults-extra-file|omit-from-file)=(.+)$/', $argument, $matches) === 1) {
                $files[$matches[1]] = file_get_contents($matches[2]);
            }
        }

        $runs[$binary] = ['command' => $process->command, 'files' => $files];

        $exitCode = $binary === 'mydumper' ? $mydumperExitCode : $myloaderExitCode;

        return Process::result(errorOutput: $exitCode === 0 ? '' : "{$binary} broke", exitCode: $exitCode);
    });

    return $runs;
}

function defaultsFilesLeftBehind(): array
{
    return glob(sys_get_temp_dir() . '/dbsync_defaults_*') ?: [];
}

it('passes the configured log level to mydumper and myloader', function () {
    config(['dbsync.verbosity' => 3]);

    $runs = fakeDumpTools();

    $this->artisan('db:production-sync')->assertSuccessful();

    expect($runs['mydumper']['command'])->toContain('--verbose=3')
        ->and($runs['myloader']['command'])->toContain('--verbose=3');
});

it('dumps with mydumper and keeps the password out of the command line', function () {
    $runs = fakeDumpTools();

    $this->artisan('db:production-sync')->assertSuccessful();

    $command = $runs['mydumper']['command'];

    expect($command)
        ->toContain('--database=production', '--threads=8', '--clear', '--sync-thread-lock-mode=NO_LOCK', '--triggers')
        ->and(implode(' ', $command))->not->toContain('pa$s');

    expect($runs['mydumper']['files']['defaults-extra-file'])->toBe(implode("\n", [
        '[mydumper]',
        'host = db.example.com',
        'port = 3306',
        'user = cloud',
        'password = pa$s w;rd\'"`x',
        'ssl-mode = VERIFY_IDENTITY',
        'ca = /etc/ssl/cert.pem',
        '[`production`.`activity_log`]',
        'where = 1=0',
        '[`production`.`failed_jobs`]',
        'where = 1=0',
        '',
    ]));
});

it('skips ignored tables entirely', function () {
    config(['dbsync.ignore' => 'sessions,cache']);
    $runs = fakeDumpTools();

    $this->artisan('db:production-sync')->assertSuccessful();

    expect($runs['mydumper']['files']['omit-from-file'])->toBe("production.sessions\nproduction.cache\n");
});

it('passes no omit file when nothing is ignored', function () {
    $runs = fakeDumpTools();

    $this->artisan('db:production-sync')->assertSuccessful();

    expect($runs['mydumper']['files'])->not->toHaveKey('omit-from-file');
});

it('loads the dump with myloader into the target database, replacing existing tables', function () {
    config([
        'database.connections.mysql.host' => '127.0.0.1',
        'database.connections.mysql.port' => '3306',
        'database.connections.mysql.username' => 'root',
        'database.connections.mysql.password' => '',
        'database.connections.mysql.database' => 'wahlocal',
    ]);
    $runs = fakeDumpTools();

    $this->artisan('db:production-sync')->assertSuccessful();

    expect($runs['myloader']['command'])->toContain('--database=wahlocal', '--drop-table=DROP', '--threads=8')
        ->and($runs['myloader']['files']['defaults-extra-file'])->toBe("[myloader]\nhost = 127.0.0.1\nport = 3306\nuser = root\n");
});

it('removes the files holding credentials once it is done', function () {
    $before = defaultsFilesLeftBehind();
    fakeDumpTools();

    $this->artisan('db:production-sync')->assertSuccessful();

    expect(defaultsFilesLeftBehind())->toBe($before);
});

it('does not import when the export fails', function () {
    $before = defaultsFilesLeftBehind();
    $runs = fakeDumpTools(mydumperExitCode: 2);

    $this->artisan('db:production-sync')
        ->expectsOutputToContain('Export failed: mydumper broke')
        ->assertFailed();

    expect($runs)->not->toHaveKey('myloader')
        ->and(defaultsFilesLeftBehind())->toBe($before);
});

it('reports failure when the import fails', function () {
    fakeDumpTools(myloaderExitCode: 1);

    $this->artisan('db:production-sync')
        ->expectsOutputToContain('Import failed: myloader broke')
        ->assertFailed();
});

it('leaves the dump in place when import is disabled', function () {
    config(['dbsync.importDump' => false]);
    $runs = fakeDumpTools();

    $this->artisan('db:production-sync --directory=/tmp/dbsync_kept')
        ->expectsOutputToContain('Dump written to /tmp/dbsync_kept, import skipped')
        ->assertSuccessful();

    expect($runs)->not->toHaveKey('myloader');
});

it('escapes defaults file values for the GLib key file parser', function () {
    $escape = new ReflectionMethod(DbSyncCommand::class, 'escapeDefaultsValue');

    expect($escape->invoke(new DbSyncCommand, " lead\\ing\nline"))->toBe('\\slead\\\\ing\\nline');
});

it('exits with a failure code outside the allowed environments', function () {
    config(['dbsync.environments' => ['local']]);

    $this->artisan('db:production-sync --test')->assertFailed();
});

it('defaults the import and cleanup flags to booleans', function () {
    $config = require base_path('config/dbsync.php');

    expect($config['importDump'])->toBeTrue();
    expect($config['removeDumpAfterImport'])->toBeTrue();
});

it('removes the files holding credentials even when running a tool throws', function () {
    $before = defaultsFilesLeftBehind();
    Process::fake(fn () => throw new RuntimeException('mydumper vanished'));

    expect(fn () => $this->artisan('db:production-sync')->run())->toThrow(RuntimeException::class, 'mydumper vanished');

    expect(defaultsFilesLeftBehind())->toBe($before);
});
