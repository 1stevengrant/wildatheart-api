<?php

namespace App\Console\Commands;

use Throwable;
use Illuminate\Console\Command;
use Database\Seeders\StagingFixturesSeeder;

class SeedStagingFixtures extends Command
{
    protected $signature = 'staging:seed-fixtures {--dry-run : Show the fixture plan without writing to the database}';

    protected $description = 'Seed safe, repeatable contact fixtures for local and staging environments';

    public function __construct(private readonly StagingFixturesSeeder $seeder)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! in_array(config('app.env'), ['local', 'staging'], true)) {
            $this->error('Staging fixtures are only allowed in local and staging environments.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        try {
            $summary = $this->seeder->seed($dryRun);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->line($dryRun ? 'Dry run. No fixture records were changed.' : 'Staging fixtures seeded successfully.');
        $this->table(['Hosts', 'Events', 'Contacts', 'Interests', 'Attendees'], [[
            $summary['hosts'],
            $summary['events'],
            $summary['contacts'],
            $summary['interests'],
            $summary['attendees'],
        ]]);

        return self::SUCCESS;
    }
}
