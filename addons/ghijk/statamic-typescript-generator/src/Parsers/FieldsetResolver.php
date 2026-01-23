<?php

namespace Ghijk\StatamicTypescriptGenerator\Parsers;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class FieldsetResolver
{
    protected array $cache = [];

    protected array $resolving = [];

    protected string $fieldsetsPath;

    public function __construct()
    {
        $this->fieldsetsPath = resource_path('fieldsets');
    }

    /**
     * Resolve a field configuration, handling imports recursively.
     */
    public function resolveField(array $field, ?string $prefix = null): array
    {
        // Handle import directive
        if (isset($field['import'])) {
            return $this->resolveImport($field['import'], $field['config'] ?? [], $field['prefix'] ?? $prefix);
        }

        // Handle inline field definition
        if (isset($field['handle']) && isset($field['field'])) {
            $handle = ($prefix ?? '') . $field['handle'];

            // Check if field references another field (e.g., field: hero.hero_title)
            if (is_string($field['field'])) {
                return $this->resolveFieldReference($handle, $field['field'], $prefix);
            }

            return [
                [
                    'handle' => $handle,
                    'type' => $field['field']['type'] ?? 'text',
                    'config' => $field['field'],
                    'required' => $this->isFieldRequired($field['field']),
                    'conditional' => $this->extractConditionals($field['field']),
                ],
            ];
        }

        return [];
    }

    /**
     * Resolve an import directive to its field definitions.
     */
    public function resolveImport(string $fieldsetName, array $configOverrides = [], ?string $prefix = null): array
    {
        // Check for circular imports
        if (in_array($fieldsetName, $this->resolving)) {
            throw new \RuntimeException("Circular fieldset import detected: {$fieldsetName}. Import chain: " . implode(' -> ', $this->resolving));
        }

        // Check cache
        $cacheKey = $fieldsetName . ($prefix ?? '') . json_encode($configOverrides);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $this->resolving[] = $fieldsetName;

        try {
            $fieldset = $this->loadFieldset($fieldsetName);
            $fields = [];

            foreach ($fieldset['fields'] ?? [] as $field) {
                $resolvedFields = $this->resolveField($field, $prefix);

                // Apply config overrides
                foreach ($resolvedFields as &$resolvedField) {
                    $fieldHandle = str_replace($prefix ?? '', '', $resolvedField['handle']);
                    if (isset($configOverrides[$fieldHandle])) {
                        $resolvedField['config'] = array_merge(
                            $resolvedField['config'],
                            $configOverrides[$fieldHandle]
                        );
                        $resolvedField['required'] = $this->isFieldRequired($resolvedField['config']);
                    }
                }

                $fields = array_merge($fields, $resolvedFields);
            }

            array_pop($this->resolving);
            $this->cache[$cacheKey] = $fields;

            return $fields;
        } catch (\Exception $e) {
            array_pop($this->resolving);

            throw $e;
        }
    }

    /**
     * Load a fieldset YAML file.
     */
    protected function loadFieldset(string $name): array
    {
        $path = "{$this->fieldsetsPath}/{$name}.yaml";

        if (! File::exists($path)) {
            throw new \RuntimeException("Fieldset not found: {$name} (looked in {$path})");
        }

        return Yaml::parseFile($path) ?? [];
    }

    /**
     * Resolve a field reference (e.g., hero.hero_title).
     */
    protected function resolveFieldReference(string $handle, string $reference, ?string $prefix): array
    {
        $parts = explode('.', $reference);

        if (count($parts) !== 2) {
            return [];
        }

        [$fieldsetName, $fieldHandle] = $parts;
        $fieldsetFields = $this->resolveImport($fieldsetName);

        foreach ($fieldsetFields as $field) {
            if ($field['handle'] === $fieldHandle) {
                return [
                    array_merge($field, ['handle' => $handle]),
                ];
            }
        }

        return [];
    }

    /**
     * Determine if a field is required based on its config.
     */
    protected function isFieldRequired(array $config): bool
    {
        if (isset($config['required']) && $config['required']) {
            return true;
        }

        if (isset($config['validate'])) {
            $validate = is_array($config['validate']) ? $config['validate'] : [$config['validate']];

            return in_array('required', $validate);
        }

        return false;
    }

    /**
     * Extract conditional display rules from field config.
     */
    protected function extractConditionals(array $config): ?array
    {
        $conditionals = [];

        if (isset($config['if'])) {
            $conditionals['if'] = $config['if'];
        }

        if (isset($config['unless'])) {
            $conditionals['unless'] = $config['unless'];
        }

        if (isset($config['show_when'])) {
            $conditionals['show_when'] = $config['show_when'];
        }

        if (isset($config['hide_when'])) {
            $conditionals['hide_when'] = $config['hide_when'];
        }

        return ! empty($conditionals) ? $conditionals : null;
    }

    /**
     * Resolve sets within a Bard or Replicator field.
     */
    public function resolveSets(array $sets, string $parentHandle): array
    {
        $resolvedSets = [];

        foreach ($sets as $groupKey => $group) {
            // Handle nested set groups (Statamic v5 structure)
            if (isset($group['sets'])) {
                foreach ($group['sets'] as $setKey => $set) {
                    $resolvedSets[$setKey] = $this->resolveSetFields($set, $parentHandle, $setKey);
                }
            } else {
                // Direct set definition
                $resolvedSets[$groupKey] = $this->resolveSetFields($group, $parentHandle, $groupKey);
            }
        }

        return $resolvedSets;
    }

    /**
     * Resolve fields within a single set.
     */
    protected function resolveSetFields(array $set, string $parentHandle, string $setKey): array
    {
        $fields = [];

        foreach ($set['fields'] ?? [] as $field) {
            $resolvedFields = $this->resolveField($field);
            $fields = array_merge($fields, $resolvedFields);
        }

        return [
            'display' => $set['display'] ?? $setKey,
            'handle' => $setKey,
            'fields' => $fields,
            'icon' => $set['icon'] ?? null,
            'instructions' => $set['instructions'] ?? null,
        ];
    }

    /**
     * Clear the resolution cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
