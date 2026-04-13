<?php

namespace App\Console\Commands\Redirects;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Services\RedirectService;
use App\DataTransferObjects\RedirectData;

class SyncDailyReadingAliasRedirectsCommand extends Command
{
    private const string SYSTEM_ORIGIN = 'daily_reading_alias';

    protected $signature = 'redirects:sync-daily-reading-aliases
                            {--path= : Absolute path to dailyreadings.json (default: storage/app/public/dailyreadings.json)}
                            {--dry-run : Show how many redirects would sync without calling Cloudflare or updating the database}
                            {--chunk=2000 : Number of redirects to send per bulk request (max 10000)}';

    protected $description = 'Create or update Cloudflare KV and database redirects from legacy daily reading aliases in dailyreadings.json';

    public function handle(RedirectService $redirectService): int
    {
        $path = $this->option('path') ?: storage_path('app/public/dailyreadings.json');
        $chunkSize = max(1, min(10000, (int) $this->option('chunk')));

        try {
            $redirects = $this->redirectsFromDailyReadingsJson($path);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $count = count($redirects);
        $this->info("Prepared {$count} daily-reading alias redirect(s) from {$path}.");

        if ($count === 0) {
            $this->warn('Nothing to sync (all aliases already match their title slug, or JSON had no valid rows).');

            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $sample = array_slice($redirects, 0, 10);
            $this->table(
                ['Source', 'Destination'],
                collect($sample)->map(fn ($r) => [$r->source, $r->destination])->all()
            );
            if ($count > 10) {
                $this->line('… ' . ($count - 10) . ' more not shown.');
            }
            $this->warn('Dry run: no changes were made.');

            return Command::SUCCESS;
        }

        $chunks = array_chunk($redirects, $chunkSize);
        foreach ($chunks as $index => $chunk) {
            $this->info('Syncing chunk ' . ($index + 1) . ' of ' . count($chunks) . ' (' . count($chunk) . ' redirects)…');
            $redirectService->createBulk($chunk);
        }

        $this->info("Finished syncing {$count} redirect(s).");

        return Command::SUCCESS;
    }

    /**
     * Build KV/database redirects from legacy Drupal alias paths in dailyreadings.json
     * to the canonical path derived from the episode title (same rule as the app slug).
     *
     * @return RedirectData[]
     *
     * @throws Exception
     */
    private function redirectsFromDailyReadingsJson(string $jsonPath): array
    {
        if (! is_readable($jsonPath)) {
            throw new Exception("Daily readings JSON not found or not readable: {$jsonPath}");
        }

        $jsonContents = file_get_contents($jsonPath);
        if ($jsonContents === false) {
            throw new Exception("Could not read file: {$jsonPath}");
        }

        $dailyReadingsData = json_decode($jsonContents, true);

        if (! is_array($dailyReadingsData)) {
            throw new Exception('Invalid daily readings JSON: ' . json_last_error_msg());
        }

        /** @var array<string, RedirectData> $bySource */
        $bySource = [];

        foreach ($dailyReadingsData as $reading) {
            if (! isset($reading['alias'], $reading['title']) || ! is_string($reading['alias']) || ! is_string($reading['title'])) {
                continue;
            }

            $source = $reading['alias'];

            if (! str_starts_with($source, '/')) {
                $source = '/' . $source;
            }

            $destination = '/daily-readings/' . Str::slug($reading['title'], '-');

            if ($source === $destination) {
                continue;
            }

            $bySource[$source] = new RedirectData(
                source: $source,
                destination: $destination,
                status_code: null,
                count: null,
                system_origin: self::SYSTEM_ORIGIN,
            );
        }

        return array_values($bySource);
    }
}
