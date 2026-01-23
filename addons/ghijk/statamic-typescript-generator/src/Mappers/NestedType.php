<?php

namespace Ghijk\StatamicTypescriptGenerator\Mappers;

class NestedType
{
    public function __construct(
        public string $name,
        public string $kind, // 'interface' or 'union'
        public array $fields = [],
        public array $members = [] // For union types
    ) {}

    /**
     * Generate TypeScript code for this nested type.
     */
    public function toTypeScript(string $indent = '    '): string
    {
        if ($this->kind === 'union') {
            return $this->toUnionTypeScript($indent);
        }

        return $this->toInterfaceTypeScript($indent);
    }

    /**
     * Generate interface TypeScript.
     */
    protected function toInterfaceTypeScript(string $indent): string
    {
        $lines = ["{$indent}interface {$this->name} {"];

        foreach ($this->fields as $field) {
            if ($field instanceof TypeMapping) {
                $lines[] = $field->toTypeScript($indent . '    ');
            }
        }

        $lines[] = "{$indent}}";

        return implode("\n", $lines);
    }

    /**
     * Generate union type TypeScript.
     */
    protected function toUnionTypeScript(string $indent): string
    {
        if (count($this->members) <= 3) {
            $union = implode(' | ', $this->members);

            return "{$indent}type {$this->name} = {$union};";
        }

        // Multi-line union for better readability
        $lines = ["{$indent}type {$this->name} ="];
        foreach ($this->members as $i => $member) {
            $prefix = $i === 0 ? '    | ' : '    | ';
            $lines[] = "{$indent}{$prefix}{$member}";
        }
        $lines[count($lines) - 1] .= ';';

        return implode("\n", $lines);
    }
}
