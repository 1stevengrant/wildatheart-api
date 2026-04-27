<?php

namespace Ghijk\StatamicTypescriptGenerator\Parsers;

use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class BlueprintParser
{
    public function __construct(
        protected FieldsetResolver $fieldsetResolver
    ) {}

    /**
     * Parse a blueprint file and return normalized field definitions.
     */
    public function parse(string $path): ParsedBlueprint
    {
        if (! File::exists($path)) {
            throw new \RuntimeException("Blueprint not found: {$path}");
        }

        $blueprint = Yaml::parseFile($path);
        $fields = $this->extractFields($blueprint);

        return new ParsedBlueprint(
            handle: $this->getHandleFromPath($path),
            title: $blueprint['title'] ?? $this->getHandleFromPath($path),
            namespace: $this->getNamespaceFromPath($path),
            fields: $fields,
            path: $path
        );
    }

    /**
     * Extract all fields from blueprint structure (tabs -> sections -> fields).
     */
    protected function extractFields(array $blueprint): array
    {
        $fields = [];

        // Handle tabbed structure
        if (isset($blueprint['tabs'])) {
            foreach ($blueprint['tabs'] as $tabKey => $tab) {
                $tabFields = $this->extractFieldsFromTab($tab);
                $fields = array_merge($fields, $tabFields);
            }
        }

        // Handle flat fields structure (some blueprints don't use tabs)
        if (isset($blueprint['fields'])) {
            foreach ($blueprint['fields'] as $field) {
                $resolvedFields = $this->fieldsetResolver->resolveField($field);
                $fields = array_merge($fields, $resolvedFields);
            }
        }

        // Handle sections without tabs
        if (isset($blueprint['sections'])) {
            foreach ($blueprint['sections'] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    $resolvedFields = $this->fieldsetResolver->resolveField($field);
                    $fields = array_merge($fields, $resolvedFields);
                }
            }
        }

        return $this->deduplicateFields($fields);
    }

    /**
     * Extract fields from a tab structure.
     */
    protected function extractFieldsFromTab(array $tab): array
    {
        $fields = [];

        foreach ($tab['sections'] ?? [] as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $resolvedFields = $this->fieldsetResolver->resolveField($field);
                $fields = array_merge($fields, $resolvedFields);
            }
        }

        // Some tabs have direct fields without sections
        foreach ($tab['fields'] ?? [] as $field) {
            $resolvedFields = $this->fieldsetResolver->resolveField($field);
            $fields = array_merge($fields, $resolvedFields);
        }

        return $fields;
    }

    /**
     * Remove duplicate field handles, keeping the last definition.
     */
    protected function deduplicateFields(array $fields): array
    {
        $unique = [];

        foreach ($fields as $field) {
            $unique[$field['handle']] = $field;
        }

        return array_values($unique);
    }

    /**
     * Extract handle from file path.
     */
    protected function getHandleFromPath(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        return Str::studly($filename);
    }

    /**
     * Determine namespace from file path.
     */
    protected function getNamespaceFromPath(string $path): string
    {
        $relativePath = Str::after($path, 'blueprints/');
        $parts = explode('/', dirname($relativePath));

        // Map directory to namespace
        $namespaceMap = config('statamic-typescript.sub_namespaces', [
            'collections' => 'Collections',
            'globals' => 'Globals',
            'taxonomies' => 'Taxonomies',
            'vendor/runway' => 'Runway',
        ]);

        $namespace = [];

        foreach ($parts as $part) {
            // Check for direct mapping
            $checkPath = implode('/', array_slice($parts, 0, array_search($part, $parts) + 1));
            if (isset($namespaceMap[$checkPath])) {
                $namespace = [$namespaceMap[$checkPath]];

                continue;
            }

            // Check for single part mapping
            if (isset($namespaceMap[$part])) {
                $namespace[] = $namespaceMap[$part];
            } elseif ($part !== '.' && ! empty($part)) {
                // Use studly case for unmapped parts
                $namespace[] = Str::studly($part);
            }
        }

        return implode('.', $namespace);
    }

    /**
     * Discover all blueprint files in the blueprints directory.
     */
    public function discoverBlueprints(?array $includePatterns = null, ?array $excludePatterns = null): array
    {
        $basePath = resource_path('blueprints');
        $includePatterns = $includePatterns ?? config('statamic-typescript.include', ['*']);
        $excludePatterns = $excludePatterns ?? config('statamic-typescript.exclude', []);

        $blueprints = [];

        foreach ($includePatterns as $pattern) {
            // Try pattern/* (blueprints in subdirectories like collections/events/*.yaml)
            $files = File::glob("{$basePath}/{$pattern}/*.yaml");
            $blueprints = array_merge($blueprints, $this->filterExcluded($files, $basePath, $excludePatterns));

            // Try pattern/*.yaml directly (blueprints like vendor/runway/*.yaml)
            $directFiles = File::glob("{$basePath}/{$pattern}.yaml");
            $blueprints = array_merge($blueprints, $this->filterExcluded($directFiles, $basePath, $excludePatterns));

            // If pattern contains *, glob it as-is for direct yaml files
            if (str_contains($pattern, '*')) {
                $wildcardFiles = File::glob("{$basePath}/{$pattern}.yaml");
                $blueprints = array_merge($blueprints, $this->filterExcluded($wildcardFiles, $basePath, $excludePatterns));
            }
        }

        return array_unique($blueprints);
    }

    /**
     * Filter out excluded files from a list.
     */
    protected function filterExcluded(array $files, string $basePath, array $excludePatterns): array
    {
        $result = [];

        foreach ($files as $file) {
            $relativePath = Str::after($file, "{$basePath}/");

            $excluded = false;
            foreach ($excludePatterns as $excludePattern) {
                if (fnmatch($excludePattern, $relativePath)) {
                    $excluded = true;

                    break;
                }
            }

            if (! $excluded) {
                $result[] = $file;
            }
        }

        return $result;
    }
}
