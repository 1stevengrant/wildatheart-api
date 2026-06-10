<?php

namespace Ghijk\StatamicTypescriptGenerator\Generators;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Ghijk\StatamicTypescriptGenerator\Mappers\FieldTypeMapper;
use Ghijk\StatamicTypescriptGenerator\Parsers\BlueprintParser;
use Ghijk\StatamicTypescriptGenerator\Parsers\ParsedBlueprint;
use Ghijk\StatamicTypescriptGenerator\Parsers\FieldsetResolver;
use Ghijk\StatamicTypescriptGenerator\Registry\SharedSetRegistry;

class PhpDtoGenerator
{
    /**
     * Fieldtypes that are control-panel UI only and serialize no data.
     */
    protected const SKIP_TYPES = [
        'section',
        'spacer',
        'revealer',
        'html',
        'trigger_astro_build',
    ];

    protected array $generatedClasses = [];

    protected array $nestedDtos = [];

    protected string $currentNamespace = '';

    protected string $currentBlueprintName = '';

    protected string $basePath = '';

    protected bool $linkFieldGenerated = false;

    /**
     * Tracks which shared base reference DTOs have already been generated this run.
     *
     * @var array<string, bool>
     */
    protected array $baseDtosGenerated = [];

    protected SharedSetRegistry $sharedRegistry;

    public function __construct(
        protected BlueprintParser $blueprintParser,
        protected FieldTypeMapper $fieldTypeMapper,
        protected FieldsetResolver $fieldsetResolver
    ) {
        $this->sharedRegistry = new SharedSetRegistry;
    }

    /**
     * Generate PHP DTO classes for all blueprints.
     */
    public function generate(array $options = []): array
    {
        $this->basePath = $options['output_path'] ?? app_path('StatamicDTO/Blueprints');
        $collections = $options['collections'] ?? null;

        $this->generatedClasses = [];
        $this->linkFieldGenerated = false;
        $this->baseDtosGenerated = [];
        $this->sharedRegistry->clear();

        // Discover blueprints
        $blueprintPaths = $this->blueprintParser->discoverBlueprints();

        // Filter by collections if specified
        if ($collections) {
            $blueprintPaths = array_filter($blueprintPaths, function ($path) use ($collections) {
                foreach ($collections as $collection) {
                    if (str_contains($path, "collections/{$collection}/")) {
                        return true;
                    }
                }

                return false;
            });
        }

        // Parse each blueprint and generate DTOs
        foreach ($blueprintPaths as $path) {
            try {
                $parsed = $this->blueprintParser->parse($path);
                $this->generateDto($parsed);
            } catch (\Exception $e) {
                logger()->warning("Failed to parse blueprint: {$path}", ['error' => $e->getMessage()]);
            }
        }

        // Generate shared DTOs
        $this->generateSharedDtos();

        return [
            'classes_count' => count($this->generatedClasses),
            'blueprints_count' => count($blueprintPaths),
            'shared_types_count' => count($this->sharedRegistry->getAll()),
            'output_path' => $this->basePath,
        ];
    }

    /**
     * Write file only if content has changed.
     */
    protected function writeFileIfChanged(string $filePath, string $newContent): void
    {
        // If file exists, compare content
        if (File::exists($filePath)) {
            $existingContent = File::get($filePath);

            // Only write if content differs
            if ($existingContent === $newContent) {
                return;
            }
        }

        // Write the file (either new file or changed content)
        File::put($filePath, $newContent);
    }

    /**
     * Generate a PHP DTO class for a blueprint.
     */
    protected function generateDto(ParsedBlueprint $blueprint): void
    {
        $className = $blueprint->getInterfaceName();
        $this->currentBlueprintName = $className;
        $this->currentNamespace = $this->getPhpNamespace($blueprint);
        $filePath = $this->getFilePath($blueprint);

        // Reset nested DTOs for this blueprint
        $this->nestedDtos = [];

        // Ensure directory exists
        $directory = dirname($filePath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $code = $this->generateClassCode($blueprint, $className, $this->currentNamespace);

        $this->writeFileIfChanged($filePath, $code);
        $this->generatedClasses[] = $filePath;

        // Generate blueprint-specific nested DTOs (non-shared types)
        foreach ($this->nestedDtos as $nestedDto) {
            $nestedPath = $this->getNestedDtoPath($nestedDto['namespace'], $nestedDto['className']);
            $nestedDir = dirname($nestedPath);

            if (! File::isDirectory($nestedDir)) {
                File::makeDirectory($nestedDir, 0755, true);
            }

            $this->writeFileIfChanged($nestedPath, $nestedDto['code']);
            $this->generatedClasses[] = $nestedPath;
        }
    }

    /**
     * Generate all shared DTOs after processing blueprints.
     */
    protected function generateSharedDtos(): void
    {
        $sharedPath = dirname($this->basePath) . '/Shared';

        if (! File::isDirectory($sharedPath)) {
            File::makeDirectory($sharedPath, 0755, true);
        }

        foreach ($this->sharedRegistry->getGeneratedCode() as $className => $code) {
            $filePath = "{$sharedPath}/{$className}.php";
            $this->writeFileIfChanged($filePath, $code);
            $this->generatedClasses[] = $filePath;
        }
    }

    /**
     * Generate PHP class code for a blueprint.
     */
    protected function generateClassCode(ParsedBlueprint $blueprint, string $className, string $namespace): string
    {
        $imports = [
            'Spatie\\LaravelData\\Data',
            'Spatie\\TypeScriptTransformer\\Attributes\\TypeScript',
        ];

        $properties = [];

        // Add common properties (will be skipped if field already exists)
        $addedFields = ['id'];
        $properties[] = ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null];

        foreach ($blueprint->fields as $field) {
            // Skip if already added
            if (in_array($field['handle'], $addedFields, true)) {
                continue;
            }

            $phpTypeInfo = $this->resolvePhpType($field, $className, $imports, []);

            if ($phpTypeInfo === null) {
                continue;
            }

            $properties[] = [
                'name' => $field['handle'],
                'type' => $phpTypeInfo['type'],
                'nullable' => $phpTypeInfo['nullable'],
                'docblock' => $phpTypeInfo['docblock'] ?? null,
                'default' => $phpTypeInfo['default'] ?? null,
            ];

            $addedFields[] = $field['handle'];
        }

        // Add runtime fields if enabled
        if (config('statamic-typescript.include_runtime_fields', false)) {
            $runtimeFields = $this->getRuntimeFields($blueprint);

            foreach ($runtimeFields as $name => $config) {
                // Skip if already added from blueprint
                if (in_array($name, $addedFields, true)) {
                    continue;
                }

                // Runtime fields may reference a shared base DTO by class name.
                if (! empty($config['import'])) {
                    $shortName = class_basename($config['import']);
                    $this->ensureBaseDto($shortName);
                    $imports[] = $config['import'];
                }

                $properties[] = [
                    'name' => $name,
                    'type' => $config['type'],
                    'nullable' => $config['nullable'] ?? false,
                    'docblock' => $config['docblock'] ?? null,
                    'default' => $config['default'] ?? null,
                ];

                $addedFields[] = $name;
            }
        }

        return $this->renderClass($className, $namespace, $imports, $properties);
    }

    /**
     * Get runtime fields for a blueprint based on its type.
     */
    protected function getRuntimeFields(ParsedBlueprint $blueprint): array
    {
        $ns = $blueprint->namespace;
        $runtimeConfig = config('statamic-typescript.runtime_fields', []);

        if (str_starts_with($ns, 'Collections')) {
            return $runtimeConfig['collections'] ?? [];
        }

        if (str_starts_with($ns, 'Globals')) {
            return $runtimeConfig['globals'] ?? [];
        }

        if (str_starts_with($ns, 'Taxonomies')) {
            return $runtimeConfig['taxonomies'] ?? [];
        }

        return [];
    }

    /**
     * Resolve PHP type for a field based on its YAML definition.
     *
     * @param  array  $nestingPath  Track the nesting path for sub-namespace generation
     */
    protected function resolvePhpType(array $field, string $parentClass, array &$imports, array $nestingPath): ?array
    {
        $handle = $field['handle'];
        $fieldType = $field['type'] ?? 'text';
        $config = $field['config'] ?? [];
        $required = $field['required'] ?? false;

        // UI-only fieldtypes carry no serialized data; omit them from the DTO.
        if (in_array($fieldType, self::SKIP_TYPES, true)) {
            return null;
        }

        return match ($fieldType) {
            // Text types
            'text', 'textarea', 'markdown', 'code', 'slug', 'color', 'hidden', 'width', 'template', 'icon', 'form' => [
                'type' => 'string',
                'nullable' => ! $required,
                'docblock' => null,
            ],

            // Reference-list types serialize to an array of handles.
            'collections', 'taxonomies', 'navs', 'sites', 'structures', 'user_roles', 'user_groups' => [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => '/** @var string[]|null */',
            ],

            // Number types
            'integer', 'range' => [
                'type' => 'int',
                'nullable' => ! $required,
                'docblock' => null,
            ],

            'float' => [
                'type' => 'float',
                'nullable' => ! $required,
                'docblock' => null,
            ],

            // Boolean
            'toggle' => [
                'type' => 'bool',
                'nullable' => ! $required,
                'docblock' => null,
            ],

            // Date/Time
            'date', 'time' => [
                'type' => 'string',
                'nullable' => ! $required,
                'docblock' => '/** @var string ISO 8601 date/time */',
            ],

            // Select/Radio with options - generate string union type in docblock
            'select', 'radio', 'button_group' => $this->resolveSelectType($config, $required),

            // Checkboxes - array of selected values
            'checkboxes' => [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => '/** @var string[] */',
            ],

            // Assets
            'assets' => $this->resolveAssetType($config, $required, $imports),

            // Link
            'link' => $this->resolveLinkType($required, $imports),

            // Entries reference
            'entries' => $this->resolveEntriesType($config, $required, $imports),

            // Terms reference
            'terms' => $this->resolveTermsType($config, $required, $imports),

            // Users reference
            'users' => $this->resolveUsersType($config, $required, $imports),

            // Runway relationships
            'belongs_to' => [
                'type' => 'int',
                'nullable' => true,
                'docblock' => null,
            ],

            // Table
            'table' => $this->resolveTableType($required, $imports),

            // Bard - rich text or structured blocks
            'bard' => $this->resolveBardType($field, $parentClass, $imports, $nestingPath),

            // Replicator - structured blocks
            'replicator' => $this->resolveReplicatorType($field, $parentClass, $imports, $nestingPath),

            // Grid - repeatable rows
            'grid' => $this->resolveGridType($field, $parentClass, $imports, $nestingPath),

            // Group - nested fields
            'group' => $this->resolveGroupType($field, $parentClass, $imports, $nestingPath),

            // Array/List
            'array', 'list' => [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => '/** @var array<string, mixed>|null */',
            ],

            // Video
            'video' => [
                'type' => 'string',
                'nullable' => ! $required,
                'docblock' => '/** @var string Video URL */',
            ],

            // YAML/JSON
            'yaml' => [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => '/** @var array<string, mixed>|null */',
            ],

            // Default - unknown type
            default => [
                'type' => 'mixed',
                'nullable' => false,
                'docblock' => "/** @var mixed Unknown field type: {$fieldType} */",
            ],
        };
    }

    /**
     * Resolve a Statamic `link` field as a typed shared DTO.
     */
    protected function resolveLinkType(bool $required, array &$imports): array
    {
        $this->ensureLinkFieldDto();
        $imports[] = 'App\\StatamicDTO\\Shared\\LinkField';

        return [
            'type' => 'LinkField',
            'nullable' => ! $required,
            'docblock' => null,
        ];
    }

    /**
     * Render the shared LinkField DTO once per run.
     *
     * Statamic `link` fields always serialize to the same shape, so the DTO is
     * fixed (not fingerprinted off blueprint structure like sets/groups/grids).
     */
    protected function ensureLinkFieldDto(): void
    {
        if ($this->linkFieldGenerated) {
            return;
        }

        $properties = [
            ['name' => 'url', 'type' => 'string', 'nullable' => true, 'docblock' => null],
            ['name' => 'text', 'type' => 'string', 'nullable' => true, 'docblock' => null],
            ['name' => 'newWindow', 'type' => 'bool', 'nullable' => false, 'docblock' => null, 'default' => false],
        ];

        $code = $this->renderClass(
            'LinkField',
            'App\\StatamicDTO\\Shared',
            ['Spatie\\LaravelData\\Data', 'Spatie\\TypeScriptTransformer\\Attributes\\TypeScript'],
            $properties,
        );

        $this->sharedRegistry->storeGeneratedCode('LinkField', $code);
        $this->linkFieldGenerated = true;
    }

    /**
     * Resolve select/radio type with options.
     */
    protected function resolveSelectType(array $config, bool $required): array
    {
        $options = $config['options'] ?? [];
        $multiple = $config['multiple'] ?? false;

        if (! empty($options)) {
            // Get option keys and filter out empty values
            $optionKeys = array_filter(array_keys($options), fn ($key) => $key !== '');

            if (empty($optionKeys)) {
                return [
                    'type' => 'string',
                    'nullable' => ! $required,
                    'docblock' => null,
                ];
            }

            $optionStr = implode("' | '", $optionKeys);
            $nullSuffix = $required ? '' : ' | null';

            if ($multiple) {
                // Use string[] instead of array<union> for PHPDoc compatibility
                return [
                    'type' => 'array',
                    'nullable' => ! $required,
                    'docblock' => "/** @var string[]{$nullSuffix} Options: '{$optionStr}' */",
                ];
            }

            return [
                'type' => 'string',
                'nullable' => ! $required,
                'docblock' => "/** @var '{$optionStr}'{$nullSuffix} */",
            ];
        }

        return [
            'type' => 'string',
            'nullable' => ! $required,
            'docblock' => null,
        ];
    }

    /**
     * Resolve asset type as a typed shared DTO reference.
     */
    protected function resolveAssetType(array $config, bool $required, array &$imports): array
    {
        return $this->referenceSharedDto('StatamicAsset', $config['max_files'] ?? null, $required, $imports);
    }

    /**
     * Resolve entries reference type as a typed shared DTO reference.
     */
    protected function resolveEntriesType(array $config, bool $required, array &$imports): array
    {
        return $this->referenceSharedDto('StatamicEntryReference', $config['max_items'] ?? null, $required, $imports);
    }

    /**
     * Resolve terms reference type as a typed shared DTO reference.
     */
    protected function resolveTermsType(array $config, bool $required, array &$imports): array
    {
        return $this->referenceSharedDto('StatamicTermReference', $config['max_items'] ?? null, $required, $imports);
    }

    /**
     * Resolve users reference type as a typed shared DTO reference.
     */
    protected function resolveUsersType(array $config, bool $required, array &$imports): array
    {
        return $this->referenceSharedDto('StatamicUserReference', $config['max_items'] ?? null, $required, $imports);
    }

    /**
     * Resolve a Statamic `table` field as an array of typed table rows.
     */
    protected function resolveTableType(bool $required, array &$imports): array
    {
        return $this->referenceSharedDto('StatamicTableRow', null, $required, $imports);
    }

    /**
     * Build a property definition that references a shared base DTO.
     *
     * A `single` cardinality ($maxOrNull === 1) maps to a native nullable class
     * type; anything else maps to a typed `array<Class>` so the transformer can
     * resolve it into a precise TypeScript array.
     */
    protected function referenceSharedDto(string $className, ?int $maxOrNull, bool $required, array &$imports): array
    {
        $this->ensureBaseDto($className);
        $imports[] = "App\\StatamicDTO\\Shared\\{$className}";

        if ($maxOrNull === 1) {
            return [
                'type' => $className,
                'nullable' => ! $required,
                'docblock' => null,
            ];
        }

        $nullSuffix = $required ? '' : '|null';

        return [
            'type' => 'array',
            'nullable' => ! $required,
            'docblock' => "/** @var array<{$className}>{$nullSuffix} */",
        ];
    }

    /**
     * Lazily register a shared base reference DTO once per generation run.
     */
    protected function ensureBaseDto(string $className): void
    {
        if (! empty($this->baseDtosGenerated[$className])) {
            return;
        }

        $properties = match ($className) {
            'StatamicAsset' => [
                ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'url', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'permalink', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'alt', 'type' => 'string', 'nullable' => true, 'docblock' => null],
                ['name' => 'width', 'type' => 'int', 'nullable' => true, 'docblock' => null],
                ['name' => 'height', 'type' => 'int', 'nullable' => true, 'docblock' => null],
            ],
            'StatamicEntryReference' => [
                ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'title', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'slug', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'url', 'type' => 'string', 'nullable' => true, 'docblock' => null],
                ['name' => 'collection', 'type' => 'string', 'nullable' => false, 'docblock' => null],
            ],
            'StatamicTermReference' => [
                ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'title', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'slug', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'taxonomy', 'type' => 'string', 'nullable' => false, 'docblock' => null],
            ],
            'StatamicUserReference' => [
                ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'name', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'email', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'avatar', 'type' => 'string', 'nullable' => true, 'docblock' => null],
            ],
            'StatamicTableRow' => [
                ['name' => 'cells', 'type' => 'array', 'nullable' => false, 'docblock' => '/** @var string[] */'],
            ],
            'HandleTitle' => [
                ['name' => 'handle', 'type' => 'string', 'nullable' => false, 'docblock' => null],
                ['name' => 'title', 'type' => 'string', 'nullable' => false, 'docblock' => null],
            ],
            default => throw new \InvalidArgumentException("Unknown shared base DTO: {$className}"),
        };

        $code = $this->renderClass(
            $className,
            'App\\StatamicDTO\\Shared',
            ['Spatie\\LaravelData\\Data', 'Spatie\\TypeScriptTransformer\\Attributes\\TypeScript'],
            $properties,
        );

        $this->sharedRegistry->storeGeneratedCode($className, $code);
        $this->baseDtosGenerated[$className] = true;
    }

    /**
     * Resolve Bard type - generates nested set DTOs.
     */
    protected function resolveBardType(array $field, string $parentClass, array &$imports, array $nestingPath): array
    {
        $config = $field['config'] ?? [];
        $sets = $config['sets'] ?? [];
        $required = $field['required'] ?? false;

        // If no sets, it's just HTML string
        if (empty($sets)) {
            return [
                'type' => 'string',
                'nullable' => ! $required,
                'docblock' => '/** @var string HTML content */',
            ];
        }

        // Add field handle to nesting path
        $newPath = array_merge($nestingPath, [$field['handle']]);

        // Generate DTOs for each set
        $setClasses = $this->generateSetDtos($field['handle'], $sets, $parentClass, $imports, $newPath);

        if (! empty($setClasses)) {
            // Add imports for shared types
            foreach ($setClasses as $setClass) {
                $imports[] = "App\\StatamicDTO\\Shared\\{$setClass}";
            }

            $unionType = implode('|', $setClasses);

            return [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => "/** @var array<{$unionType}>|null */",
            ];
        }

        return [
            'type' => 'array',
            'nullable' => ! $required,
            'docblock' => '/** @var array<mixed>|null */',
        ];
    }

    /**
     * Resolve Replicator type - generates nested set DTOs.
     */
    protected function resolveReplicatorType(array $field, string $parentClass, array &$imports, array $nestingPath): array
    {
        $config = $field['config'] ?? [];
        $sets = $config['sets'] ?? [];
        $required = $field['required'] ?? false;

        if (empty($sets)) {
            return [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => '/** @var array<mixed>|null */',
            ];
        }

        // Add field handle to nesting path
        $newPath = array_merge($nestingPath, [$field['handle']]);

        // Generate DTOs for each set
        $setClasses = $this->generateSetDtos($field['handle'], $sets, $parentClass, $imports, $newPath);

        if (! empty($setClasses)) {
            // Add imports for shared types
            foreach ($setClasses as $setClass) {
                $imports[] = "App\\StatamicDTO\\Shared\\{$setClass}";
            }

            $unionType = implode('|', $setClasses);

            return [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => "/** @var array<{$unionType}>|null */",
            ];
        }

        return [
            'type' => 'array',
            'nullable' => ! $required,
            'docblock' => '/** @var array<mixed>|null */',
        ];
    }

    /**
     * Generate DTOs for Bard/Replicator sets.
     */
    protected function generateSetDtos(string $fieldHandle, array $sets, string $parentClass, array &$imports, array $nestingPath): array
    {
        $setClasses = [];

        foreach ($sets as $groupKey => $group) {
            // Handle nested set groups (Statamic v5 structure)
            if (isset($group['sets'])) {
                foreach ($group['sets'] as $setKey => $set) {
                    $className = $this->generateSetDto($fieldHandle, $setKey, $set, $parentClass, $imports, $nestingPath);
                    if ($className) {
                        $setClasses[] = $className;
                    }
                }
            } else {
                // Direct set definition
                $className = $this->generateSetDto($fieldHandle, $groupKey, $group, $parentClass, $imports, $nestingPath);
                if ($className) {
                    $setClasses[] = $className;
                }
            }
        }

        return array_unique($setClasses);
    }

    /**
     * Generate a single set DTO - uses shared registry for deduplication.
     */
    protected function generateSetDto(string $fieldHandle, string $setKey, array $set, string $parentClass, array &$imports, array $nestingPath): ?string
    {
        // Resolve fields first
        $resolvedFields = [];
        foreach ($set['fields'] ?? [] as $field) {
            $resolved = $this->fieldsetResolver->resolveField($field);
            $resolvedFields = array_merge($resolvedFields, $resolved);
        }

        // Calculate fingerprint based on structure
        $fingerprint = $this->sharedRegistry->calculateFingerprint($resolvedFields, $setKey);

        // Check if we already have this structure
        if ($this->sharedRegistry->has($fingerprint)) {
            return $this->sharedRegistry->get($fingerprint);
        }

        // Generate a suggested name based on set key
        $suggestedName = Str::studly($setKey) . 'Set';

        // Register and get the actual class name
        $className = $this->sharedRegistry->register($fingerprint, $suggestedName, $resolvedFields, $setKey);

        // Generate the DTO code
        $sharedNamespace = 'App\\StatamicDTO\\Shared';
        $sharedImports = [
            'Spatie\\LaravelData\\Data',
            'Spatie\\TypeScriptTransformer\\Attributes\\TypeScript',
        ];

        $properties = [];

        // Track added fields to prevent duplicates
        $addedFields = ['type', 'id', 'enabled'];

        // Add discriminator type property
        $properties[] = [
            'name' => 'type',
            'type' => 'string',
            'nullable' => false,
            'docblock' => "/** @var '{$setKey}' */",
            'default' => $setKey,
        ];

        // Add ID property
        $properties[] = ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null];

        // Add enabled property
        $properties[] = ['name' => 'enabled', 'type' => 'bool', 'nullable' => false, 'docblock' => null, 'default' => true];

        // Add resolved fields
        foreach ($resolvedFields as $resolvedField) {
            // Skip if already added as common property
            if (in_array($resolvedField['handle'], $addedFields, true)) {
                continue;
            }

            // Add to nesting path
            $newPath = array_merge($nestingPath, [$setKey]);

            $phpTypeInfo = $this->resolvePhpType($resolvedField, $className, $sharedImports, $newPath);

            if ($phpTypeInfo === null) {
                continue;
            }

            $properties[] = [
                'name' => $resolvedField['handle'],
                'type' => $phpTypeInfo['type'],
                'nullable' => $phpTypeInfo['nullable'],
                'docblock' => $phpTypeInfo['docblock'] ?? null,
                'default' => $phpTypeInfo['default'] ?? null,
            ];

            $addedFields[] = $resolvedField['handle'];
        }

        $code = $this->renderClass($className, $sharedNamespace, $sharedImports, $properties);
        $this->sharedRegistry->storeGeneratedCode($className, $code);

        return $className;
    }

    /**
     * Resolve Grid type - generates row DTO.
     */
    protected function resolveGridType(array $field, string $parentClass, array &$imports, array $nestingPath): array
    {
        $config = $field['config'] ?? [];
        $fields = $config['fields'] ?? [];
        $required = $field['required'] ?? false;

        if (empty($fields)) {
            return [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => '/** @var array<mixed>|null */',
            ];
        }

        // Add field handle to nesting path
        $newPath = array_merge($nestingPath, [$field['handle']]);

        // Generate a row DTO
        $rowClassName = $this->generateGridRowDto($field['handle'], $fields, $parentClass, $imports, $newPath);

        if ($rowClassName) {
            $imports[] = "App\\StatamicDTO\\Shared\\{$rowClassName}";

            return [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => "/** @var array<{$rowClassName}>|null */",
            ];
        }

        return [
            'type' => 'array',
            'nullable' => ! $required,
            'docblock' => '/** @var array<mixed>|null */',
        ];
    }

    /**
     * Generate a Grid row DTO - uses shared registry.
     */
    protected function generateGridRowDto(string $fieldHandle, array $fields, string $parentClass, array &$imports, array $nestingPath): ?string
    {
        // Resolve fields first
        $resolvedFields = [];
        foreach ($fields as $field) {
            $resolved = $this->fieldsetResolver->resolveField($field);
            $resolvedFields = array_merge($resolvedFields, $resolved);
        }

        // Use 'row' as the set key for rows
        $setKey = $fieldHandle . '_row';

        // Calculate fingerprint
        $fingerprint = $this->sharedRegistry->calculateFingerprint($resolvedFields, $setKey);

        // Check if we already have this structure
        if ($this->sharedRegistry->has($fingerprint)) {
            return $this->sharedRegistry->get($fingerprint);
        }

        // Generate a suggested name
        $suggestedName = Str::studly($fieldHandle) . 'Row';

        // Register and get the actual class name
        $className = $this->sharedRegistry->register($fingerprint, $suggestedName, $resolvedFields, $setKey);

        // Generate the DTO code
        $sharedNamespace = 'App\\StatamicDTO\\Shared';
        $sharedImports = [
            'Spatie\\LaravelData\\Data',
            'Spatie\\TypeScriptTransformer\\Attributes\\TypeScript',
        ];

        $properties = [];

        // Add ID property
        $properties[] = ['name' => 'id', 'type' => 'string', 'nullable' => false, 'docblock' => null];

        foreach ($resolvedFields as $resolvedField) {
            $phpTypeInfo = $this->resolvePhpType($resolvedField, $className, $sharedImports, $nestingPath);

            if ($phpTypeInfo === null) {
                continue;
            }

            $properties[] = [
                'name' => $resolvedField['handle'],
                'type' => $phpTypeInfo['type'],
                'nullable' => $phpTypeInfo['nullable'],
                'docblock' => $phpTypeInfo['docblock'] ?? null,
                'default' => $phpTypeInfo['default'] ?? null,
            ];
        }

        $code = $this->renderClass($className, $sharedNamespace, $sharedImports, $properties);
        $this->sharedRegistry->storeGeneratedCode($className, $code);

        return $className;
    }

    /**
     * Resolve Group type - generates nested DTO.
     */
    protected function resolveGroupType(array $field, string $parentClass, array &$imports, array $nestingPath): array
    {
        $config = $field['config'] ?? [];
        $fields = $config['fields'] ?? [];
        $required = $field['required'] ?? false;

        if (empty($fields)) {
            return [
                'type' => 'array',
                'nullable' => ! $required,
                'docblock' => null,
            ];
        }

        // Add field handle to nesting path
        $newPath = array_merge($nestingPath, [$field['handle']]);

        // Generate a group DTO
        $groupClassName = $this->generateGroupDto($field['handle'], $fields, $parentClass, $imports, $newPath);

        if ($groupClassName) {
            $imports[] = "App\\StatamicDTO\\Shared\\{$groupClassName}";

            return [
                'type' => $groupClassName,
                'nullable' => ! $required,
                'docblock' => null,
            ];
        }

        return [
            'type' => 'array',
            'nullable' => ! $required,
            'docblock' => null,
        ];
    }

    /**
     * Generate a Group DTO - uses shared registry.
     */
    protected function generateGroupDto(string $fieldHandle, array $fields, string $parentClass, array &$imports, array $nestingPath): ?string
    {
        // Resolve fields first
        $resolvedFields = [];
        foreach ($fields as $field) {
            $resolved = $this->fieldsetResolver->resolveField($field);
            $resolvedFields = array_merge($resolvedFields, $resolved);
        }

        // Use 'group' as the set key for groups
        $setKey = $fieldHandle . '_group';

        // Calculate fingerprint
        $fingerprint = $this->sharedRegistry->calculateFingerprint($resolvedFields, $setKey);

        // Check if we already have this structure
        if ($this->sharedRegistry->has($fingerprint)) {
            return $this->sharedRegistry->get($fingerprint);
        }

        // Generate a suggested name
        $suggestedName = Str::studly($fieldHandle) . 'Group';

        // Register and get the actual class name
        $className = $this->sharedRegistry->register($fingerprint, $suggestedName, $resolvedFields, $setKey);

        // Generate the DTO code
        $sharedNamespace = 'App\\StatamicDTO\\Shared';
        $sharedImports = [
            'Spatie\\LaravelData\\Data',
            'Spatie\\TypeScriptTransformer\\Attributes\\TypeScript',
        ];

        $properties = [];

        foreach ($resolvedFields as $resolvedField) {
            $phpTypeInfo = $this->resolvePhpType($resolvedField, $className, $sharedImports, $nestingPath);

            if ($phpTypeInfo === null) {
                continue;
            }

            $properties[] = [
                'name' => $resolvedField['handle'],
                'type' => $phpTypeInfo['type'],
                'nullable' => $phpTypeInfo['nullable'],
                'docblock' => $phpTypeInfo['docblock'] ?? null,
                'default' => $phpTypeInfo['default'] ?? null,
            ];
        }

        $code = $this->renderClass($className, $sharedNamespace, $sharedImports, $properties);
        $this->sharedRegistry->storeGeneratedCode($className, $code);

        return $className;
    }

    /**
     * Render the PHP class.
     */
    protected function renderClass(string $className, string $namespace, array $imports, array $properties): string
    {
        $imports = array_unique($imports);
        sort($imports);

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = '/**';
        $lines[] = ' * ============================================================';
        $lines[] = ' * AUTO-GENERATED FILE - DO NOT EDIT';
        $lines[] = ' * ============================================================';
        $lines[] = ' *';
        $lines[] = ' * This file was automatically generated by the Statamic';
        $lines[] = ' * TypeScript Generator addon. Any manual changes will be';
        $lines[] = ' * overwritten the next time the generator runs.';
        $lines[] = ' *';
        $lines[] = ' * To modify this DTO, update the corresponding Statamic';
        $lines[] = ' * blueprint or fieldset, then run:';
        $lines[] = ' *';
        $lines[] = ' *   php artisan statamic:generate-types';
        $lines[] = ' * ============================================================';
        $lines[] = ' */';
        $lines[] = '';
        $lines[] = "namespace {$namespace};";
        $lines[] = '';

        foreach ($imports as $import) {
            $lines[] = "use {$import};";
        }

        $lines[] = '';
        $lines[] = '#[TypeScript]';
        $lines[] = "class {$className} extends Data";
        $lines[] = '{';

        foreach ($properties as $prop) {
            if (! empty($prop['docblock'])) {
                $lines[] = "    {$prop['docblock']}";
            }

            $type = $prop['type'];
            // Handle nullable types:
            // - mixed already includes null
            // - union types (int|float) need | null syntax instead of ?
            if ($prop['nullable']) {
                if ($type === 'mixed') {
                    // mixed already includes null, don't add nullable marker
                } elseif (str_contains($type, '|')) {
                    // Union types need | null syntax
                    $type = $type . '|null';
                } else {
                    $type = '?' . $type;
                }
            }

            $declaration = "    public {$type} \${$prop['name']}";

            if (isset($prop['default']) && $prop['default'] !== null) {
                if (is_string($prop['default'])) {
                    $declaration .= " = '{$prop['default']}'";
                } elseif (is_bool($prop['default'])) {
                    $declaration .= ' = ' . ($prop['default'] ? 'true' : 'false');
                } else {
                    $declaration .= ' = ' . var_export($prop['default'], true);
                }
            } elseif ($prop['nullable']) {
                $declaration .= ' = null';
            }

            $declaration .= ';';
            $lines[] = $declaration;
            $lines[] = '';
        }

        // Remove trailing empty line
        if (end($lines) === '') {
            array_pop($lines);
        }

        $lines[] = '}';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Get PHP namespace from blueprint.
     */
    protected function getPhpNamespace(ParsedBlueprint $blueprint): string
    {
        $baseNamespace = 'App\\StatamicDTO\\Blueprints';

        // Add sub-namespace based on type
        $ns = $blueprint->namespace;

        if (str_starts_with($ns, 'Collections.')) {
            $collection = Str::after($ns, 'Collections.');
            $collection = str_replace('.', '\\', $collection);

            return $baseNamespace . '\\Collections\\' . $collection;
        }

        if (str_starts_with($ns, 'Globals')) {
            return $baseNamespace . '\\Globals';
        }

        if (str_starts_with($ns, 'Taxonomies.')) {
            $taxonomy = Str::after($ns, 'Taxonomies.');
            $taxonomy = str_replace('.', '\\', $taxonomy);

            return $baseNamespace . '\\Taxonomies\\' . $taxonomy;
        }

        return $baseNamespace;
    }

    /**
     * Get file path for a blueprint DTO.
     */
    protected function getFilePath(ParsedBlueprint $blueprint): string
    {
        $namespace = $this->getPhpNamespace($blueprint);
        $relativePath = str_replace('App\\StatamicDTO\\Blueprints', '', $namespace);
        $relativePath = str_replace('\\', '/', $relativePath);

        return "{$this->basePath}{$relativePath}/{$blueprint->getInterfaceName()}.php";
    }

    /**
     * Get file path for a nested DTO.
     */
    protected function getNestedDtoPath(string $namespace, string $className): string
    {
        $relativePath = str_replace('App\\StatamicDTO\\Blueprints', '', $namespace);
        $relativePath = str_replace('\\', '/', $relativePath);

        return "{$this->basePath}{$relativePath}/{$className}.php";
    }
}
