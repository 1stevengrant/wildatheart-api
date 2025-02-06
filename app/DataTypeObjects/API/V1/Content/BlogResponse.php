<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\DataTypeObjects\API\V1\EntryResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BlogResponse extends EntryResponse
{
    public string $published_date;

    public string $content;

    public string $summary;

    public ?string $author_name;
}
