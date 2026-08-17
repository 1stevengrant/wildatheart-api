<?php

use Symfony\Component\Finder\Finder;

/**
 * A job that runs longer than the connection's retry_after gets handed to a
 * second worker while the first is still running, which inflates its attempt
 * count until it fails with MaxAttemptsExceededException without ever throwing.
 * These invariants keep that from silently coming back.
 */
function longestJobTimeout(): array
{
    $longest = ['job' => null, 'timeout' => 0];

    foreach (Finder::create()->files()->in(app_path('Jobs'))->name('*.php') as $file) {
        $class = 'App\\Jobs\\' . str_replace(
            ['/', '.php'],
            ['\\', ''],
            $file->getRelativePathname()
        );

        if (! class_exists($class)) {
            continue;
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract() || ! $reflection->hasProperty('timeout')) {
            continue;
        }

        $timeout = $reflection->getProperty('timeout')->getDefaultValue();

        if (is_int($timeout) && $timeout > $longest['timeout']) {
            $longest = ['job' => $class, 'timeout' => $timeout];
        }
    }

    return $longest;
}

test('the horizon supervisor timeout covers the longest running job', function () {
    $longest = longestJobTimeout();

    foreach (config('horizon.defaults') as $name => $supervisor) {
        expect($supervisor['timeout'])->toBeGreaterThanOrEqual(
            $longest['timeout'],
            "Horizon supervisor [{$name}] would SIGKILL [{$longest['job']}] mid-run on deploy."
        );
    }
});

test('retry_after is longer than the horizon supervisor timeout', function () {
    $retryAfter = config('queue.connections.redis.retry_after');

    foreach (config('horizon.defaults') as $name => $supervisor) {
        expect($retryAfter)->toBeGreaterThan(
            $supervisor['timeout'],
            "Redis would re-queue a job still running under supervisor [{$name}]."
        );
    }
});
