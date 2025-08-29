<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class NavItemData extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public ?string $icon,
        public bool $enabled = true,
        public string $type = 'standard_nav_link',
    ) {}

    /**
     * Create from Statamic data array
     */
    public static function fromStatamic(array $data): self
    {
        return new self(
            id: $data['id'] ?? uniqid(),
            title: $data['title'],
            url: $data['url'],
            icon: $data['icon'] ?? null,
            enabled: $data['enabled'] ?? true,
            type: $data['type'] ?? 'standard_nav_link',
        );
    }
}
