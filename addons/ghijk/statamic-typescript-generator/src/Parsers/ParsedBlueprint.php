<?php

namespace Ghijk\StatamicTypescriptGenerator\Parsers;

class ParsedBlueprint
{
    public function __construct(
        public string $handle,
        public string $title,
        public string $namespace,
        public array $fields,
        public string $path
    ) {}

    /**
     * Get the full TypeScript interface name.
     */
    public function getInterfaceName(): string
    {
        return $this->handle;
    }

    /**
     * Get the full namespace path including interface name.
     */
    public function getFullTypeName(): string
    {
        if (empty($this->namespace)) {
            return $this->handle;
        }

        return "{$this->namespace}.{$this->handle}";
    }

    /**
     * Check if this is a Runway (Eloquent model) blueprint.
     */
    public function isRunway(): bool
    {
        return str_starts_with($this->namespace, 'Runway');
    }
}
