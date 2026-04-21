<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\Enums\ContentStatus;
use Spatie\LaravelData\Data;
use App\DataTypeObjects\Blueprint;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AlertResponse extends Data
{
    public string $id;

    public string $title;

    public bool $private = false;

    public bool $published = true;

    public string $status = ContentStatus::PUBLISHED->value;

    public Blueprint $blueprint;

    public string $locale = 'default';

    public string $content;

    public string $start_date;

    public string $end_date;

    public string $link_text;

    public string $link_url;
}
