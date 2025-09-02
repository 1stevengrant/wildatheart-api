<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class FeaturedContentData extends Data
{
    public function __construct(
        /** @var array<string> */
        public array $show_in_regions,
        public ?string $featured_content_url,
        public ?string $featured_content_text,
        public ?string $featured_content_image,
    ) {}

    public static function fromStatamicData($data): self
    {
        return new self(
            show_in_regions: $data->get('show_in_regions', []),
            featured_content_url: $data->get('featured_content_url'),
            featured_content_text: $data->get('featured_content_text'),
            featured_content_image: $data->get('featured_content_image'),
        );
    }
}
