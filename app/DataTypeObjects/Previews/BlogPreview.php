<?php

namespace App\DataTypeObjects\Previews;

use App\DataTypeObjects\ContentPreview;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BlogPreview extends ContentPreview
{


  public ?string $author_name = null;

  public ?string $author_image_src = null;

  public ?array $tags = null;
}
