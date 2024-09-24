<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MediaItemsSlide extends Data
{
    function __construct()
    {
        $this->slides = [];
    }

    public $type = 'media_items_from_series';
    /** @var array<int, SlideBlock> */
    public array $slides;
}
