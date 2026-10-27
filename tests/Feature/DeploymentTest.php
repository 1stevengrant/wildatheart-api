<?php

use Illuminate\Support\Facades\Artisan;

test('deployment artisan commands execute without failure', function () {
    // Store original config values
    $originalMaintenanceMode = app()->isDownForMaintenance();

    $redisAvailable = rescue(fn () => app('redis')->connection()->ping(), false, false);

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
        $redisAvailable ? 'horizon:terminate' : null,
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
    } finally {
        // The caches are built from the testing environment, so leaving them
        // behind points the local app at the test database.
        foreach (['config:clear', 'route:clear', 'event:clear', 'view:clear'] as $command) {
            Artisan::call($command);
        }
    }

    // Cleanup - restore original maintenance mode state if needed
    if ($originalMaintenanceMode !== app()->isDownForMaintenance()) {
        Artisan::call($originalMaintenanceMode ? 'down' : 'up');
    }
});
