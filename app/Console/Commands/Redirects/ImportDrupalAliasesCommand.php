<?php

namespace App\Console\Commands\Redirects;

use App\DataTransferObjects\RedirectData;
use App\Jobs\Admin\AddRedirectsInBulkFromArrayJob;
use Exception;
use App\Models\Redirect;
use Barryvdh\LaravelIdeHelper\Alias;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportDrupalAliasesCommand extends Command
{
    protected $signature = 'app:import-drupal-aliases {--dry-run : Show what would be imported without actually importing}';

    protected $description = 'Imports URL aliases from Drupal using the Drupal database connection';

    public const array EXCLUDED_SOURCE_PATHS = ['user', 'file/', 'taxonomy/'];

    public const array EXCLUDED_ALIAS_PATHS = ['allies/'];

    public const string DRUPAL_CONNECTION = 'drupal';

    public function handle(): int
    {
        $this->info('Importing Drupal URL aliases...');

        try {
            // Test Drupal database connection
            $this->info('Testing Drupal database connection...');
            DB::connection(self::DRUPAL_CONNECTION)->getPdo();
            $this->info('✓ Connection successful!');

            // Get filtered aliases from Drupal
            $this->info('Fetching URL aliases from Drupal...');
            $drupalAliases = $this->getDrupalAliases();

            $this->info("Found {$drupalAliases->count()} URL aliases to import");

            if ($drupalAliases->isEmpty()) {
                $this->warn('No URL aliases found to import.');

                return 0;
            }

            // Show preview if dry run
            if ($this->option('dry-run')) {
                $this->showPreview($drupalAliases);

                return 0;
            }

            // Import aliases as redirects
            $this->info('Importing URL aliases as redirects...');
            $imported = $this->importAliases($drupalAliases);

            $this->info("✓ Successfully imported {$imported} URL aliases as redirects");
        } catch (Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    private function getDrupalAliases(): Collection
    {
        return DB::connection(self::DRUPAL_CONNECTION)
            ->table('url_alias')
            ->select('source', 'alias')
            ->where(function ($query) {
                // Exclude source paths
                foreach (self::EXCLUDED_SOURCE_PATHS as $path) {
                    $query->where('source', 'not like', "%$path%");
                }

                // Exclude alias paths
                foreach (self::EXCLUDED_ALIAS_PATHS as $alias) {
                    $query->where('alias', 'not like', "%$alias%");
                }
            })
            ->whereNotNull('source')
            ->whereNotNull('alias')
            ->get();
    }

    private function showPreview($aliases): void
    {
        $this->info('Preview of URL aliases to be imported as redirects:');
        $this->newLine();

        $headers = ['Source (becomes destination)', 'Alias (becomes source)'];
        $rows = $aliases->map(function ($alias) {
            return [
                $alias->source,
                $alias->alias,
            ];
        })->toArray();

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('Note: URL aliases will be converted to redirects where:');
        $this->info('- Alias becomes the redirect source (what users type)');
        $this->info('- Source becomes the redirect destination (where they go)');
        $this->newLine();
        $this->info('Run without --dry-run to perform the actual import.');
    }

    private function importAliases($drupalAliases): int
    {
        $imported = 0;
        $bar = $this->output->createProgressBar($drupalAliases->count());
        $bar->start();

        // First find the aliases that are duplicates
        $groupedAliases = $drupalAliases->groupBy('source');
        $duplicateSources = $groupedAliases->filter(function ($aliases) {
            return $aliases->count() > 1;
        });


        $nonDuplicateAliases = $drupalAliases->filter(function ($alias) use ($duplicateSources) {
            return !$duplicateSources->has($alias->source);
        })->map(function ($alias) {
            $source = cleanPath($alias->source);
            $destination = cleanPath($alias->alias);
            $redirect = new RedirectData($source, $destination, 301, 0, 'drupal_alias');
            return $redirect;
        });

        $duplicateRedirects = [];
        foreach ($duplicateSources as $duplicateSource) {

            for ($i = 0; $i < $duplicateSource->count(); $i++) {
                $alias = $duplicateSource[$i];
                // keep the first alias as the primary redirect
                if ($i === 0) {
                    $source = cleanPath($alias->source);
                    $destination = cleanPath($alias->alias);
                    $nonDuplicateAliases[] = new RedirectData($source, $destination, 301, 0, 'drupal_alias');
                } else {
                    // add the rest of the aliases as duplicate redirects using the alias as the source
                    $source = cleanPath($alias->alias);
                    $destination = cleanPath($alias->source);
                    $duplicateRedirects[] = new RedirectData($source, $destination, 301, 0, 'drupal_alias');
                }
            }
        }

        if (!empty($duplicateRedirects)) {
            AddRedirectsInBulkFromArrayJob::dispatch($duplicateRedirects);
        }


        if ($duplicateSources->isNotEmpty()) {
            $this->warn("\nFound " . $duplicateSources->count() . " sources with multiple aliases:");
        }

        foreach ($nonDuplicateAliases->chunk(9999) as $chunk) {
            $count = 0;
            $redirects = [];
            foreach ($chunk as $alias) {


                try {
                    // Clean and validate the alias data
                    // For URL aliases, the 'alias' becomes the source (what users type)
                    $source = cleanPath($alias->source);
                    $destination = cleanPath($alias->destination);


                    if (empty($source) || empty($destination) || !$alias) {
                        continue;
                    }

                    $redirects[] = $alias;
                    $imported++;
                } catch (Exception $e) {
                    $this->warn("Failed to import alias: $alias->destination -> $alias->source");
                    $this->warn("Error: {$e->getMessage()}");
                }

                $bar->advance();
            }
            AddRedirectsInBulkFromArrayJob::dispatch($redirects);
        }

        $bar->finish();
        $this->newLine();

        return $imported;
    }
}
