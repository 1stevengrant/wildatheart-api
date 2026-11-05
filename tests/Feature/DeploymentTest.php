<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

test('deployment artisan commands execute without failure', function () {
    // Store original config values
    $originalMaintenanceMode = app()->isDownForMaintenance();

    $redisAvailable = rescue(fn () => app('redis')->connection()->ping(), false, false);

    // Parallel workers boot from bootstrap/cache and the compiled view directory,
    // so the caches built here must stay private to this test.
    $cacheDirectory = sys_get_temp_dir() . '/deployment-test-' . getmypid();
    $cachePaths = [
        'APP_CONFIG_CACHE' => "{$cacheDirectory}/config.php",
        'APP_ROUTES_CACHE' => "{$cacheDirectory}/routes.php",
        'APP_EVENTS_CACHE' => "{$cacheDirectory}/events.php",
    ];

    File::ensureDirectoryExists("{$cacheDirectory}/views");
    config(['view.compiled' => "{$cacheDirectory}/views"]);

    foreach ($cachePaths as $key => $path) {
        $_ENV[$key] = $path;
    }

    // Array of commands to test
    $commands = array_filter([
        'cache:clear',
        'config:clear',
        'config:cache',
        'view:clear',
        'view:cache',
        'event:clear',
        'event:cache',
        'route:clear',
        'route:cache',
        $redisAvailable ? 'queue:restart' : null,
        'migrate --force',
    ]);

    try {
        foreach ($commands as $command) {
            try {
                $commandParts = explode(' ', $command);
                $baseCommand = $commandParts[0];
                $arguments = array_slice($commandParts, 1);

                $exitCode = Artisan::call($baseCommand, $arguments ? [$arguments[0] => true] : []);

                expect($exitCode)->toBe(0)
                    ->and(Artisan::output())->not->toContain('error')
                    ->and(Artisan::output())->not->toContain('Exception');

            } catch (Exception $e) {
                $this->fail("Command '$command' failed: " . $e->getMessage());
            }
        }

        foreach ($cachePaths as $path) {
            expect($path)->toBeFile();
        }
    } finally {
        foreach (array_keys($cachePaths) as $key) {
            unset($_ENV[$key]);
        }

        File::deleteDirectory($cacheDirectory);
    }

    // Cleanup - restore original maintenance mode state if needed
    if ($originalMaintenanceMode !== app()->isDownForMaintenance()) {
        Artisan::call($originalMaintenanceMode ? 'down' : 'up');
    }
});
