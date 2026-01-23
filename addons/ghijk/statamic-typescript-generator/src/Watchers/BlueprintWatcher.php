<?php

namespace Ghijk\StatamicTypescriptGenerator\Watchers;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\File;

class BlueprintWatcher
{
    protected array $watchPaths = [];

    protected int $debounceMs;

    protected array $lastModifiedTimes = [];

    protected float $lastChangeTime = 0;

    public function __construct()
    {
        $this->debounceMs = config('statamic-typescript.watch.debounce_ms', 500);

        $paths = config('statamic-typescript.watch.paths', ['blueprints', 'fieldsets']);

        foreach ($paths as $path) {
            $fullPath = resource_path($path);
            if (File::isDirectory($fullPath)) {
                $this->watchPaths[] = $fullPath;
            }
        }
    }

    /**
     * Start watching for changes and call the callback when detected.
     */
    public function watch(callable $onChange): void
    {
        $this->buildInitialState();

        while (true) {
            $changedFiles = $this->detectChanges();

            if (! empty($changedFiles)) {
                $now = microtime(true) * 1000;

                // Debounce
                if (($now - $this->lastChangeTime) >= $this->debounceMs) {
                    $this->lastChangeTime = $now;
                    $onChange($changedFiles);
                }
            }

            // Sleep for a short interval before checking again
            usleep(100000); // 100ms
        }
    }

    /**
     * Build the initial state of file modification times.
     */
    protected function buildInitialState(): void
    {
        $this->lastModifiedTimes = [];

        foreach ($this->getWatchedFiles() as $file) {
            $this->lastModifiedTimes[$file] = filemtime($file);
        }
    }

    /**
     * Detect any files that have changed.
     */
    protected function detectChanges(): array
    {
        $changed = [];
        $currentFiles = $this->getWatchedFiles();

        // Check for modified or new files
        foreach ($currentFiles as $file) {
            $mtime = filemtime($file);

            if (! isset($this->lastModifiedTimes[$file])) {
                // New file
                $this->lastModifiedTimes[$file] = $mtime;
                $changed[] = $file;
            } elseif ($this->lastModifiedTimes[$file] < $mtime) {
                // Modified file
                $this->lastModifiedTimes[$file] = $mtime;
                $changed[] = $file;
            }
        }

        // Check for deleted files
        $deletedFiles = array_diff(array_keys($this->lastModifiedTimes), $currentFiles);
        foreach ($deletedFiles as $file) {
            unset($this->lastModifiedTimes[$file]);
            $changed[] = $file;
        }

        return $changed;
    }

    /**
     * Get all YAML files in watched directories.
     */
    protected function getWatchedFiles(): array
    {
        $files = [];

        foreach ($this->watchPaths as $path) {
            if (! File::isDirectory($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'yaml') {
                    $files[] = $file->getRealPath();
                }
            }
        }

        return $files;
    }

    /**
     * Get the configured watch paths.
     */
    public function getWatchPaths(): array
    {
        return $this->watchPaths;
    }
}
