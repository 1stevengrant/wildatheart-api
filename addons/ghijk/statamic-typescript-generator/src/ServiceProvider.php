<?php

namespace Ghijk\StatamicTypescriptGenerator;

use Statamic\Providers\AddonServiceProvider;
use Ghijk\StatamicTypescriptGenerator\Commands\GenerateTypesCommand;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        GenerateTypesCommand::class,
    ];

    public function bootAddon(): void
    {
        $this->publishes([
            __DIR__ . '/../config/statamic-typescript.php' => config_path('statamic-typescript.php'),
        ], 'statamic-typescript-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/statamic-typescript.php',
            'statamic-typescript'
        );
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(Registry\SharedSetRegistry::class);
        $this->app->singleton(Parsers\FieldsetResolver::class);
        $this->app->singleton(Parsers\BlueprintParser::class);
        $this->app->singleton(Mappers\FieldTypeMapper::class);
        $this->app->singleton(Generators\TypeScriptGenerator::class);
        $this->app->singleton(Generators\PhpDtoGenerator::class);
    }
}
