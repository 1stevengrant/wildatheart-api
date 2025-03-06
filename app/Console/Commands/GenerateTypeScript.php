<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;

class GenerateTypeScript extends Command
{
    protected $signature = 'app:generate-typescript';

    protected $description = 'Generate TypeScript types from Statamic blueprints.';

    public function handle(): void
    {
        $config = TypeScriptTransformerConfig::create()
            // path where your PHP classes are
            ->autoDiscoverTypes(base_path('app/DataTypeObjects'))
            // list of transformers
            ->transformers([DtoTransformer::class])
            // file where TypeScript type definitions will be written
            ->formatter(PrettierFormatter::class)
            ->outputFile(base_path() . '/js/generated.d.ts');

        TypeScriptTransformer::create($config)->transform();

        $this->info('TypeScript types generated successfully.');
    }
}
