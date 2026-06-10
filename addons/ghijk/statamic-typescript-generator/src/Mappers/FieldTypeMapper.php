<?php

namespace Ghijk\StatamicTypescriptGenerator\Mappers;

use Ghijk\StatamicTypescriptGenerator\Parsers\FieldsetResolver;

class FieldTypeMapper
{
    /**
     * Fieldtypes that should be skipped (UI-only, no data).
     */
    protected const SKIP_TYPES = [
        'section',
        'spacer',
        'revealer',
        'html',
        'trigger_astro_build',
    ];

    /**
     * Simple string types.
     */
    protected const STRING_TYPES = [
        'text',
        'textarea',
        'slug',
        'markdown',
        'code',
        'color',
        'video',
        'template',
        'width',
        'icon',
        'yaml',
        'hidden',
    ];

    /**
     * Number types.
     */
    protected const NUMBER_TYPES = [
        'integer',
        'range',
        'float',
    ];

    public function __construct(
        protected FieldsetResolver $fieldsetResolver
    ) {}

    /**
     * Map a field to its TypeScript type.
     */
    public function mapField(array $field, string $parentHandle = ''): ?TypeMapping
    {
        $type = $field['type'] ?? 'text';
        $config = $field['config'] ?? [];
        $handle = $field['handle'];
        $required = $field['required'] ?? false;
        $hasConditional = ! empty($field['conditional']);

        // Skip UI-only fields
        if (in_array($type, self::SKIP_TYPES)) {
            return null;
        }

        $tsType = match (true) {
            // Simple string types
            in_array($type, self::STRING_TYPES, true) => 'string',

            // Number types
            in_array($type, self::NUMBER_TYPES, true) => 'number',

            // Boolean
            $type === 'toggle' => 'boolean',

            // Date/Time (ISO strings)
            $type === 'date', $type === 'time' => 'string',

            // Select types with options
            $type === 'select', $type === 'radio', $type === 'button_group' => $this->mapSelectType($config),

            // Multiple select
            $type === 'checkboxes' => $this->mapCheckboxesType($config),

            // Assets
            $type === 'assets' => $this->mapAssetsType($config),

            // Relationship types
            $type === 'entries' => $this->mapEntriesType($config),
            $type === 'terms' => $this->mapTermsType($config),
            $type === 'users' => $this->mapUsersType($config),

            // Runway relationships
            $type === 'belongs_to' => 'number | null',
            $type === 'has_many' => $this->mapHasManyType($config),

            // Link
            $type === 'link' => 'StatamicLink',

            // Complex types
            $type === 'bard' => $this->mapBardType($config, $handle, $parentHandle),
            $type === 'replicator' => $this->mapReplicatorType($config, $handle, $parentHandle),
            $type === 'grid' => $this->mapGridType($config, $handle, $parentHandle),
            $type === 'group' => $this->mapGroupType($config, $handle, $parentHandle),

            // Array types
            in_array($type, ['array', 'list', 'taggable', 'tags'], true) => 'string[]',

            // Table
            $type === 'table' => 'StatamicTableRow[]',

            // Dictionary
            $type === 'dictionary' => 'Record<string, string>',

            // Form selection
            $type === 'form' => 'string',

            // Collections/Taxonomies/Navs/Sites/Structures (reference types)
            in_array($type, ['collections', 'taxonomies', 'navs', 'sites', 'structures'], true) => 'string[]',

            // User roles/groups
            $type === 'user_roles', $type === 'user_groups' => 'string[]',

            // Default: unknown type
            default => 'unknown',
        };

        // Handle conditional fields - make them optional
        $isOptional = $hasConditional || ! $required;

        return new TypeMapping(
            handle: $handle,
            type: $tsType,
            required: ! $isOptional,
            comment: $type === 'unknown' ? "Unknown fieldtype: {$type}" : null,
            nestedTypes: $this->getNestedTypes($type, $config, $handle, $parentHandle)
        );
    }

    /**
     * Map select/radio/button_group to union type or string.
     */
    protected function mapSelectType(array $config): string
    {
        $options = $config['options'] ?? [];

        if (empty($options)) {
            return 'string';
        }

        $values = $this->extractOptionValues($options);

        if (empty($values)) {
            return 'string';
        }

        return implode(' | ', array_map(fn ($v) => "'{$v}'", $values));
    }

    /**
     * Map checkboxes to array of union or string[].
     */
    protected function mapCheckboxesType(array $config): string
    {
        $options = $config['options'] ?? [];

        if (empty($options)) {
            return 'string[]';
        }

        $values = $this->extractOptionValues($options);

        if (empty($values)) {
            return 'string[]';
        }

        $union = implode(' | ', array_map(fn ($v) => "'{$v}'", $values));

        return "({$union})[]";
    }

    /**
     * Extract option values from various formats.
     */
    protected function extractOptionValues(array $options): array
    {
        $values = [];

        foreach ($options as $key => $value) {
            // Format: key: label
            if (is_string($key) && is_string($value)) {
                $values[] = $key;
            }
            // Format: [{key: 'value', value: 'Label'}]
            elseif (is_array($value) && isset($value['key'])) {
                $values[] = $value['key'];
            }
            // Format: ['value1', 'value2']
            elseif (is_int($key) && is_string($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * Map assets field based on max_files.
     */
    protected function mapAssetsType(array $config): string
    {
        $maxFiles = $config['max_files'] ?? null;

        if ($maxFiles === 1) {
            return 'StatamicAsset | null';
        }

        return 'StatamicAsset[]';
    }

    /**
     * Map entries field based on max_items.
     */
    protected function mapEntriesType(array $config): string
    {
        $maxItems = $config['max_items'] ?? null;

        if ($maxItems === 1) {
            return 'StatamicEntryReference | null';
        }

        return 'StatamicEntryReference[]';
    }

    /**
     * Map terms field based on max_items.
     */
    protected function mapTermsType(array $config): string
    {
        $maxItems = $config['max_items'] ?? null;

        if ($maxItems === 1) {
            return 'StatamicTermReference | null';
        }

        return 'StatamicTermReference[]';
    }

    /**
     * Map users field based on max_items.
     */
    protected function mapUsersType(array $config): string
    {
        $maxItems = $config['max_items'] ?? null;

        if ($maxItems === 1) {
            return 'StatamicUserReference | null';
        }

        return 'StatamicUserReference[]';
    }

    /**
     * Map has_many (Runway) field.
     */
    protected function mapHasManyType(array $config): string
    {
        $resource = $config['resource'] ?? null;

        if ($resource) {
            // Convert resource name to type reference
            $typeName = str_replace('_', '', ucwords($resource, '_'));

            return "Runway.{$typeName}[]";
        }

        return 'unknown[]';
    }

    /**
     * Map Bard field.
     */
    protected function mapBardType(array $config, string $handle, string $parentHandle): string
    {
        // If Bard has sets, it returns complex content
        if (isset($config['sets']) && ! empty($config['sets'])) {
            $setTypeName = $this->generateSetTypeName($handle, $parentHandle);

            return "{$setTypeName}[]";
        }

        // Plain Bard without sets returns HTML string
        return 'string';
    }

    /**
     * Map Replicator field.
     */
    protected function mapReplicatorType(array $config, string $handle, string $parentHandle): string
    {
        if (isset($config['sets']) && ! empty($config['sets'])) {
            $setTypeName = $this->generateSetTypeName($handle, $parentHandle);

            return "{$setTypeName}[]";
        }

        return 'unknown[]';
    }

    /**
     * Map Grid field.
     */
    protected function mapGridType(array $config, string $handle, string $parentHandle): string
    {
        $rowTypeName = $this->generateRowTypeName($handle, $parentHandle);

        return "{$rowTypeName}[]";
    }

    /**
     * Map Group field.
     */
    protected function mapGroupType(array $config, string $handle, string $parentHandle): string
    {
        // Group fields are inlined as nested object
        return $this->generateGroupTypeName($handle, $parentHandle);
    }

    /**
     * Get nested types that need to be generated.
     */
    protected function getNestedTypes(string $type, array $config, string $handle, string $parentHandle): array
    {
        return match ($type) {
            'bard', 'replicator' => $this->generateSetNestedTypes($config, $handle, $parentHandle),
            'grid' => $this->generateGridNestedTypes($config, $handle, $parentHandle),
            'group' => $this->generateGroupNestedTypes($config, $handle, $parentHandle),
            default => [],
        };
    }

    /**
     * Generate nested types for Bard/Replicator sets.
     */
    protected function generateSetNestedTypes(array $config, string $handle, string $parentHandle): array
    {
        $sets = $config['sets'] ?? [];

        if (empty($sets)) {
            return [];
        }

        $resolvedSets = $this->fieldsetResolver->resolveSets($sets, $handle);
        $nestedTypes = [];
        $unionMembers = [];

        foreach ($resolvedSets as $setKey => $set) {
            $setTypeName = $this->generateSetTypeName($handle, $parentHandle) . $this->pascalCase($setKey) . 'Set';
            $unionMembers[] = $setTypeName;

            $fields = [
                new TypeMapping('type', "'{$setKey}'", true),
                new TypeMapping('id', 'string', true),
            ];

            foreach ($set['fields'] ?? [] as $field) {
                $mapped = $this->mapField($field, $handle);
                if ($mapped) {
                    $fields[] = $mapped;
                    // Collect any nested types from the field
                    $nestedTypes = array_merge($nestedTypes, $mapped->nestedTypes);
                }
            }

            $nestedTypes[] = new NestedType(
                name: $setTypeName,
                kind: 'interface',
                fields: $fields
            );
        }

        // Add the union type
        $unionTypeName = $this->generateSetTypeName($handle, $parentHandle);
        $nestedTypes[] = new NestedType(
            name: $unionTypeName,
            kind: 'union',
            members: $unionMembers
        );

        return $nestedTypes;
    }

    /**
     * Generate nested types for Grid rows.
     */
    protected function generateGridNestedTypes(array $config, string $handle, string $parentHandle): array
    {
        $gridFields = $config['fields'] ?? [];
        $rowTypeName = $this->generateRowTypeName($handle, $parentHandle);

        $fields = [
            new TypeMapping('id', 'string', true),
        ];

        $nestedTypes = [];

        foreach ($gridFields as $field) {
            $fieldConfig = $field['field'] ?? [];
            $fieldHandle = $field['handle'] ?? '';

            $mapped = $this->mapField([
                'handle' => $fieldHandle,
                'type' => $fieldConfig['type'] ?? 'text',
                'config' => $fieldConfig,
                'required' => false,
            ], $handle);

            if ($mapped) {
                $fields[] = $mapped;
                $nestedTypes = array_merge($nestedTypes, $mapped->nestedTypes);
            }
        }

        $nestedTypes[] = new NestedType(
            name: $rowTypeName,
            kind: 'interface',
            fields: $fields
        );

        return $nestedTypes;
    }

    /**
     * Generate nested types for Group fields.
     */
    protected function generateGroupNestedTypes(array $config, string $handle, string $parentHandle): array
    {
        $groupFields = $config['fields'] ?? [];
        $nestedTypes = [];

        // Resolve imported fields
        $resolvedFields = [];
        foreach ($groupFields as $field) {
            $resolved = $this->fieldsetResolver->resolveField($field);
            $resolvedFields = array_merge($resolvedFields, $resolved);
        }

        $fields = [];
        foreach ($resolvedFields as $field) {
            $mapped = $this->mapField($field, $handle);
            if ($mapped) {
                $fields[] = $mapped;
                $nestedTypes = array_merge($nestedTypes, $mapped->nestedTypes);
            }
        }

        $groupTypeName = $this->generateGroupTypeName($handle, $parentHandle);
        $nestedTypes[] = new NestedType(
            name: $groupTypeName,
            kind: 'interface',
            fields: $fields
        );

        return $nestedTypes;
    }

    /**
     * Generate a type name for sets.
     */
    protected function generateSetTypeName(string $handle, string $parentHandle): string
    {
        $prefix = $parentHandle ? $this->pascalCase($parentHandle) : '';

        return $prefix . $this->pascalCase($handle) . 'Set';
    }

    /**
     * Generate a type name for grid rows.
     */
    protected function generateRowTypeName(string $handle, string $parentHandle): string
    {
        $prefix = $parentHandle ? $this->pascalCase($parentHandle) : '';

        return $prefix . $this->pascalCase($handle) . 'Row';
    }

    /**
     * Generate a type name for groups.
     */
    protected function generateGroupTypeName(string $handle, string $parentHandle): string
    {
        $prefix = $parentHandle ? $this->pascalCase($parentHandle) : '';

        return $prefix . $this->pascalCase($handle) . 'Group';
    }

    /**
     * Convert string to PascalCase.
     */
    protected function pascalCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $string)));
    }
}
