<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\DataTypeObjects\API\V1\EntryResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class BlogResponse extends EntryResponse
{
    public string $published_date;

    public ?string $author_name;

    public string $summary;

    #[LiteralTypeScriptType('Array<Record<string, unknown>> | string | null')]
    public array|string|null $content;

    #[LiteralTypeScriptType('Array<Record<string, unknown>> | string | null')]
    public array|string|null $featured_image_src;

    public string $api_url;
}
