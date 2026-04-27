<?php

namespace Ghijk\StatamicTypescriptGenerator\Registry;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

/**
 * Registry for tracking and deduplicating structurally identical sets.
 *
 * Sets with identical field structures are stored once in the Shared namespace
 * and reused across all blueprints, dramatically reducing file count.
 */
class SharedSetRegistry
{
    /**
     * Map of fingerprint => shared class name.
     */
    protected array $registry = [];

    /**
     * Map of fingerprint => set data (for generating shared DTOs).
     */
    protected array $setData = [];

    /**
     * Generated shared DTO code, indexed by class name.
     */
    protected array $generatedCode = [];

    /**
     * Counter for generating unique names when needed.
     */
    protected array $nameCounter = [];

    /**
     * Calculate a fingerprint for a set based on its structure.
     * Identical structures will produce identical fingerprints.
     */
    public function calculateFingerprint(array $fields, string $setKey): string
    {
        $normalized = $this->normalizeFields($fields);

        return md5(json_encode([
            'set_key' => $setKey,
            'fields' => $normalized,
        ]));
    }

    /**
     * Normalize fields for fingerprinting (remove position-dependent data).
     */
    protected function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            $normalizedField = [
                'handle' => $field['handle'] ?? '',
                'type' => $field['type'] ?? 'text',
                'required' => $field['required'] ?? false,
            ];

            // Include config that affects structure
            $config = $field['config'] ?? [];

            // For select/radio/button_group, include options
            if (in_array($normalizedField['type'], ['select', 'radio', 'button_group', 'checkboxes'])) {
                $normalizedField['options'] = $this->normalizeOptions($config['options'] ?? []);
            }

            // For assets/entries/terms/users, include max_items
            if (in_array($normalizedField['type'], ['assets', 'entries', 'terms', 'users'])) {
                $normalizedField['max_items'] = $config['max_items'] ?? $config['max_files'] ?? null;
            }

            // For nested types, recursively fingerprint
            if (in_array($normalizedField['type'], ['bard', 'replicator']) && isset($config['sets'])) {
                $normalizedField['sets'] = $this->normalizeSets($config['sets']);
            }

            if ($normalizedField['type'] === 'grid' && isset($config['fields'])) {
                $normalizedField['grid_fields'] = $this->normalizeFields($config['fields']);
            }

            if ($normalizedField['type'] === 'group' && isset($config['fields'])) {
                $normalizedField['group_fields'] = $this->normalizeFields($config['fields']);
            }

            $normalized[$field['handle'] ?? ''] = $normalizedField;
        }

        // Sort by handle to ensure consistent ordering
        ksort($normalized);

        return array_values($normalized);
    }

    /**
     * Normalize options for fingerprinting.
     */
    protected function normalizeOptions(array $options): array
    {
        $keys = [];

        foreach ($options as $key => $value) {
            if (is_string($key)) {
                $keys[] = $key;
            } elseif (is_array($value) && isset($value['key'])) {
                $keys[] = $value['key'];
            } elseif (is_string($value)) {
                $keys[] = $value;
            }
        }

        sort($keys);

        return $keys;
    }

    /**
     * Normalize sets for fingerprinting.
     */
    protected function normalizeSets(array $sets): array
    {
        $normalized = [];

        foreach ($sets as $groupKey => $group) {
            if (isset($group['sets'])) {
                foreach ($group['sets'] as $setKey => $set) {
                    $normalized[$setKey] = [
                        'fields' => $this->normalizeFields($set['fields'] ?? []),
                    ];
                }
            } else {
                $normalized[$groupKey] = [
                    'fields' => $this->normalizeFields($group['fields'] ?? []),
                ];
            }
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * Check if a set with this fingerprint already exists.
     */
    public function has(string $fingerprint): bool
    {
        return isset($this->registry[$fingerprint]);
    }

    /**
     * Get the shared class name for a fingerprint.
     */
    public function get(string $fingerprint): ?string
    {
        return $this->registry[$fingerprint] ?? null;
    }

    /**
     * Register a shared set and return the class name.
     */
    public function register(string $fingerprint, string $suggestedName, array $fields, string $setKey): string
    {
        if ($this->has($fingerprint)) {
            return $this->registry[$fingerprint];
        }

        // Generate a clean class name based on the set key
        $baseName = $this->generateClassName($setKey);

        // Ensure uniqueness
        $className = $baseName;
        if (isset($this->nameCounter[$baseName])) {
            $this->nameCounter[$baseName]++;
            $className = $baseName . $this->nameCounter[$baseName];
        } else {
            $this->nameCounter[$baseName] = 1;
        }

        $this->registry[$fingerprint] = $className;
        $this->setData[$fingerprint] = [
            'className' => $className,
            'fields' => $fields,
            'setKey' => $setKey,
        ];

        return $className;
    }

    /**
     * Generate a clean class name from a set key.
     */
    protected function generateClassName(string $setKey): string
    {
        // Convert to StudlyCase and add Set suffix if not present
        $name = Str::studly($setKey);

        if (! Str::endsWith($name, 'Set') && ! Str::endsWith($name, 'Group') && ! Str::endsWith($name, 'Row')) {
            $name .= 'Set';
        }

        return $name;
    }

    /**
     * Get all registered shared sets with their data.
     */
    public function getAll(): array
    {
        return $this->setData;
    }

    /**
     * Store generated code for a shared class.
     */
    public function storeGeneratedCode(string $className, string $code): void
    {
        $this->generatedCode[$className] = $code;
    }

    /**
     * Get all generated shared code.
     */
    public function getGeneratedCode(): array
    {
        return $this->generatedCode;
    }

    /**
     * Clear the registry (useful between runs).
     */
    public function clear(): void
    {
        $this->registry = [];
        $this->setData = [];
        $this->generatedCode = [];
        $this->nameCounter = [];
    }

    /**
     * Get statistics about the registry.
     */
    public function getStats(): array
    {
        return [
            'total_shared_types' => count($this->registry),
            'types' => array_values($this->registry),
        ];
    }
}
