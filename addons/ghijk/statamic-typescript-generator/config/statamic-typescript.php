<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Output Path
    |--------------------------------------------------------------------------
    |
    | The path where the generated TypeScript definitions will be written.
    |
    */

    'output_path' => resource_path('js/types/statamic.d.ts'),

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The root namespace for generated TypeScript types.
    |
    */

    'namespace' => 'Statamic.Data',

    /*
    |--------------------------------------------------------------------------
    | Sub-namespaces
    |--------------------------------------------------------------------------
    |
    | Mapping of blueprint directories to TypeScript sub-namespaces.
    |
    */

    'sub_namespaces' => [
        'collections' => 'Collections',
        'globals' => 'Globals',
        'taxonomies' => 'Taxonomies',
        'vendor/runway' => 'Runway',
    ],

    /*
    |--------------------------------------------------------------------------
    | Include Patterns
    |--------------------------------------------------------------------------
    |
    | Glob patterns for blueprints to include in generation.
    |
    */

    'include' => [
        'collections/*',
        'globals/*',
        'taxonomies/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude Patterns
    |--------------------------------------------------------------------------
    |
    | Glob patterns for blueprints to exclude from generation.
    |
    */

    'exclude' => [
        'assets/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Runway Integration (Disabled)
    |--------------------------------------------------------------------------
    |
    | Enable TypeScript generation for Runway (Eloquent) blueprints.
    |
    */

    'runway' => [
        'enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nullable Strategy
    |--------------------------------------------------------------------------
    |
    | How to represent nullable types in TypeScript:
    | - 'union': T | null
    | - 'optional': T? (optional property)
    |
    */

    'nullable_strategy' => 'union',

    /*
    |--------------------------------------------------------------------------
    | Watch Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the file watcher mode.
    |
    */

    'watch' => [
        'debounce_ms' => 500,
        'paths' => [
            'blueprints',
            'fieldsets',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Generate Base Types
    |--------------------------------------------------------------------------
    |
    | Include these base type definitions in the output.
    |
    */

    'base_types' => [
        'StatamicAsset' => true,
        'StatamicLink' => true,
        'StatamicEntryReference' => true,
        'StatamicTermReference' => true,
        'StatamicUserReference' => true,
        'StatamicBardTextNode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Include Runtime Fields
    |--------------------------------------------------------------------------
    |
    | Include Statamic runtime fields that exist on entries/globals/terms
    | but aren't defined in the YAML blueprints.
    |
    */

    'include_runtime_fields' => true,

    'runtime_fields' => [
        'collections' => [
            'locale' => ['type' => 'string', 'default' => 'default'],
            'origin' => ['type' => 'string', 'nullable' => true],
            'published' => ['type' => 'bool', 'default' => true],
            'private' => ['type' => 'bool', 'default' => false],
            'status' => ['type' => 'string', 'default' => 'published'],
            'uri' => ['type' => 'string', 'nullable' => true],
            'url' => ['type' => 'string', 'nullable' => true],
            'permalink' => ['type' => 'string', 'nullable' => true],
            'api_url' => ['type' => 'string', 'nullable' => true],
            'collection' => ['type' => 'array', 'nullable' => true, 'docblock' => '/** @var array{handle: string, title: string}|null */'],
            'blueprint' => ['type' => 'array', 'nullable' => true, 'docblock' => '/** @var array{handle: string, title: string}|null */'],
            'last_modified' => ['type' => 'string', 'nullable' => true, 'docblock' => '/** @var string ISO 8601 date/time */'],
        ],
        'globals' => [
            'handle' => ['type' => 'string'],
            'api_url' => ['type' => 'string', 'nullable' => true],
        ],
        'taxonomies' => [
            'locale' => ['type' => 'string', 'default' => 'default'],
            'uri' => ['type' => 'string', 'nullable' => true],
            'url' => ['type' => 'string', 'nullable' => true],
            'permalink' => ['type' => 'string', 'nullable' => true],
            'api_url' => ['type' => 'string', 'nullable' => true],
            'taxonomy' => ['type' => 'array', 'nullable' => true, 'docblock' => '/** @var array{handle: string, title: string}|null */'],
        ],
    ],

];
