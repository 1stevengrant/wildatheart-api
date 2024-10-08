<?php

namespace App\DataTypeObjects\API\V1\Content\Prayers;

use App\DataTypeObjects\API\V1\EntryResponse;
use App\DataTypeObjects\File;
use App\DataTypeObjects\PlayableFile;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PrayersShowResponse extends EntryResponse
{
    public string $content;
    public string $summary;
    public string $drupal_url;
    public ?PlayableFile $mp3;
    public ?File $pdf;
}
