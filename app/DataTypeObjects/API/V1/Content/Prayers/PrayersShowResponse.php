<?php

namespace App\DataTypeObjects\API\V1\Content\Prayers;

use App\DataTypeObjects\File;
use App\DataTypeObjects\PlayableFile;
use App\DataTypeObjects\API\V1\EntryResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class PrayersShowResponse extends EntryResponse
{
    public string $content;

    #[LiteralTypeScriptType('Array<Record<string, unknown>>')]
    public array $before_content_blocks;

    #[LiteralTypeScriptType('Array<Record<string, unknown>>')]
    public array $after_content_blocks;

    public string $summary;

    public string $drupal_url;

    public ?PlayableFile $mp3;

    public ?File $pdf;
}
