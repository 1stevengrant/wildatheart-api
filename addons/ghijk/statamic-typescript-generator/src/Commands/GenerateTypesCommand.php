<?php

namespace Ghijk\StatamicTypescriptGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Ghijk\StatamicTypescriptGenerator\Watchers\BlueprintWatcher;
use Ghijk\StatamicTypescriptGenerator\Generators\PhpDtoGenerator;
use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;

class GenerateTypesCommand extends Command
{
    protected $signature = 'statamic:generate-types
                            {--watch : Watch for changes and regenerate}
                            {--skip-pint : Skip running Laravel Pint}
                            {--collections= : Limit to specific collections (comma-separated)}';

    protected $description = 'Generate PHP DTOs and TypeScript definitions from Statamic blueprints';

    public function handle(
        PhpDtoGenerator $phpGenerator,
        BlueprintWatcher $watcher
    ): int {
        $collections = $this->option('collections')
            ? explode(',', $this->option('collections'))
            : null;

        if ($this->option('watch')) {
            return $this->watchMode($phpGenerator, $watcher, $collections);
        }

        return $this->generateOnce($phpGenerator, $collections);
    }

    protected function generateOnce(
        PhpDtoGenerator $phpGenerator,
        ?array $collections
    ): int {
        // Step 1: Generate PHP DTOs
        $this->info('Generating PHP DTOs from Statamic blueprints...');

        try {
            $result = $phpGenerator->generate([
                'collections' => $collections,
            ]);

            $this->info("Generated {$result['classes_count']} PHP classes from {$result['blueprints_count']} blueprints");
            $this->info("Shared types: {$result['shared_types_count']}");
            $this->info("Output: {$result['output_path']}");
        } catch (\Exception $e) {
            $this->error("PHP DTO generation failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        // Step 2: Format with Pint
        if (! $this->option('skip-pint')) {
            $this->newLine();
            $this->runPint($result['output_path']);
        }

        // Step 3: Generate TypeScript from DTOs
        $this->newLine();
        $this->info('Generating TypeScript from DTOs...');

        try {
            $this->generateTypeScript();
            $this->info('TypeScript generated: ' . config('statamic-typescript.output_path'));
        } catch (\Exception $e) {
            $this->error("TypeScript generation failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('Done!');

        return Command::SUCCESS;
    }

    protected function watchMode(
        PhpDtoGenerator $phpGenerator,
        BlueprintWatcher $watcher,
        ?array $collections
    ): int {
        $this->info('Watching for blueprint changes... (Press Ctrl+C to stop)');
        $this->newLine();

        // Initial generation
        $this->generate($phpGenerator, $collections);

        // Watch for changes
        $watcher->watch(function (array $changedFiles) use ($phpGenerator, $collections) {
            $this->newLine();
            $this->info('Changes detected in: ' . implode(', ', array_map('basename', $changedFiles)));

            $this->generate($phpGenerator, $collections);
        });

        return Command::SUCCESS;
    }

    protected function generate(
        PhpDtoGenerator $phpGenerator,
        ?array $collections
    ): void {
        $this->info('Regenerating...');

        try {
            $result = $phpGenerator->generate([
                'collections' => $collections,
            ]);

            $this->info("Generated {$result['classes_count']} PHP classes");

            if (! $this->option('skip-pint')) {
                $this->runPint($result['output_path']);
            }

            $this->generateTypeScript();
            $this->info('TypeScript updated');
        } catch (\Exception $e) {
            $this->error("Generation failed: {$e->getMessage()}");
        }
    }

    protected function runPint(string $path): void
    {
        $this->info('Formatting with Laravel Pint...');

        $pintPath = base_path('vendor/bin/pint');

        if (! file_exists($pintPath)) {
            $this->warn('Laravel Pint not found, skipping formatting');

            return;
        }

        $result = Process::run("{$pintPath} {$path} --quiet");

        if ($result->successful()) {
            $this->info('Formatting complete');
        } else {
            $this->warn('Pint formatting had issues: ' . $result->errorOutput());
        }
    }

    protected function generateTypeScript(): void
    {
        $outputPath = config('statamic-typescript.output_path');

        $config = TypeScriptTransformerConfig::create()
            ->autoDiscoverTypes(app_path('StatamicDTO/Blueprints'))
            ->autoDiscoverTypes(app_path('StatamicDTO/Shared'))
            ->transformers([DtoTransformer::class])
            ->outputFile($outputPath);

        TypeScriptTransformer::create($config)->transform();
    }
}
