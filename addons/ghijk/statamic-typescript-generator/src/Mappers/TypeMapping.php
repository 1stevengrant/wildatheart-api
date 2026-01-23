<?php

namespace Ghijk\StatamicTypescriptGenerator\Mappers;

class TypeMapping
{
    public function __construct(
        public string $handle,
        public string $type,
        public bool $required = false,
        public ?string $comment = null,
        public array $nestedTypes = []
    ) {}

    /**
     * Get the TypeScript property definition.
     */
    public function toTypeScript(string $indent = '    '): string
    {
        $optional = $this->required ? '' : '?';
        $comment = $this->comment ? " // $this->comment" : '';

        return "$indent$this->handle$optional: $this->type;$comment";
    }
}
