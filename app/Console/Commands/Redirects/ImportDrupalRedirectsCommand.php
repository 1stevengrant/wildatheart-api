<?php

namespace App\Console\Commands\Redirects;

use Exception;
use App\Models\Redirect;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\DataTransferObjects\RedirectData;
use App\Jobs\Admin\AddRedirectsInBulkFromArrayJob;

class ImportDrupalRedirectsCommand extends Command
{
    protected $signature = 'app:import-drupal-redirects {--dry-run : Show what would be imported without actually importing}';

    protected $description = 'Imports redirects from Drupal using the Drupal database connection';

    public const array EXCLUDED_SOURCE_PATHS = ['allies/', 'events/', 'users/', 'daily-reading-old/', 'user/'];

    public const array EXCLUDED_DESTINATION_PATHS = ['my-account'];

    public const string DRUPAL_CONNECTION = 'drupal';

    public function handle(): int
    {
        $this->info('Importing Drupal redirects...');

        try {
            // Test Drupal database connection
            $this->info('Testing Drupal database connection...');
            DB::connection(self::DRUPAL_CONNECTION)->getPdo();
            $this->info('✓ Connection successful!');

            // Get filtered redirects from Drupal
            $this->info('Fetching redirects from Drupal...');
            $drupalRedirects = $this->getDrupalRedirects();

            $this->info("Found {$drupalRedirects->count()} redirects to import");

            $existingRedirects = Redirect::all()->pluck('source')->toArray();
            $redirectsToImport = $drupalRedirects->filter(function ($redirect) use ($existingRedirects) {
                return ! in_array('/' . $redirect->source, $existingRedirects);
            });
            $this->info("Found {$redirectsToImport->count()} redirects to import that don't already exist");

            if ($redirectsToImport->isEmpty()) {
                $this->warn('No redirects found to import.');

                return 0;
            }

            // Show preview if dry run
            if ($this->option('dry-run')) {
                // $this->info($drupalRedirects[0]);
                // $this->showPreview($existingRedirects);

                return 0;
            }

            // Import redirects
            $this->info('Importing redirects...');
            $imported = $this->importRedirects($redirectsToImport);

            $this->info("✓ Successfully imported $imported redirects");
        } catch (Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    private function getDrupalRedirects(): Collection
    {
        return DB::connection(self::DRUPAL_CONNECTION)
            ->table('redirect')
            ->select('source', 'redirect', 'status_code', 'count', 'access')
            ->where(function ($query) {
                // Exclude source paths
                foreach (self::EXCLUDED_SOURCE_PATHS as $path) {
                    $query->where('source', 'not like', "%$path%");
                }

                // Exclude destination paths
                foreach (self::EXCLUDED_DESTINATION_PATHS as $destination) {
                    $query->where('redirect', 'not like', "%$destination%");
                }
            })
            ->whereNotNull('source')
            ->whereNotNull('redirect')
            ->get();
    }

    private function showPreview($redirects): void
    {
        $this->info('Preview of redirects to be imported:');
        $this->newLine();

        $headers = ['Source', 'Destination'];
        $rows = $redirects->map(function ($redirect) {
            return [
                $redirect->source,
                $redirect->redirect,
                $redirect->status_code,
                $redirect->count,
                $redirect->access,
            ];
        })->toArray();

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('Run without --dry-run to perform the actual import.');
    }

    private function importRedirects($drupalRedirects): int
    {
        $imported = 0;
        $bar = $this->output->createProgressBar($drupalRedirects->count());
        $bar->start();

        $redirects = [];
        foreach ($drupalRedirects as $redirect) {
            try {
                // Clean and validate the redirect data
                $source = cleanPath($redirect->source);
                $destination = $this->cleanDestination($redirect->redirect);
                $statusCode = $redirect->status_code ?? 301;

                if (empty($source) || empty($destination)) {
                    continue;
                }

                $source = cleanPath($source);
                $destination = cleanPath($destination);
                $redirects[] = new RedirectData($source, $destination, $statusCode, $redirect->count ?? 0, 'drupal_redirect');

                $imported++;
            } catch (Exception $e) {
                $this->warn("Failed to import redirect: $redirect->source -> $redirect->redirect");
                $this->warn("Error: {$e->getMessage()}");
            }

            $bar->advance();
        }

        AddRedirectsInBulkFromArrayJob::dispatch($redirects);

        $bar->finish();
        $this->newLine();

        return $imported;
    }

    private function cleanDestination(string $destination): string
    {
        // Handle internal paths (like internal:/node/123)
        if (str_starts_with($destination, 'internal:')) {
            $destination = str_replace('internal:', '', $destination);
        }

        // Handle entity paths (like entity:node/123)
        if (str_starts_with($destination, 'entity:')) {
            $destination = str_replace('entity:', '/', $destination);
        }

        // Clean up the destination
        $destination = mb_trim($destination);

        // If it's not already a full URL, ensure it starts with /
        if (! str_contains($destination, '://') && ! str_starts_with($destination, '/')) {
            $destination = '/' . $destination;
        }

        return $destination;
    }
}
