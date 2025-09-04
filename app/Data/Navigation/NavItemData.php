<?php

namespace App\Data\Navigation;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\LaravelData\Attributes\Validation\StringType;

#[TypeScript]
class NavItemData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $id,

        #[Required, StringType]
        public string $title,

        #[Required, Url]
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
