<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\DataTypeObjects\API\V1\EntryResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BlogResponse extends EntryResponse
{
    public string $published_date;

    public ?string $author_name;

    public string $summary;

    /** Rendered HTML, or the raw string content when the entry is not block based */
    public ?string $content;

    /** Bunny CDN URL of the vertical image */
    public ?string $featured_image_src;

    public string $api_url;
}
