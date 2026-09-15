<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The `images` group of an entry serialised with Entry::toArray(): one fully augmented asset per crop.
 */
#[TypeScript]
class StatamicAssetImageSet extends Data
{
    public ?StatamicAssetData $wide = null;

    public ?StatamicAssetData $square = null;

    public ?StatamicAssetData $vertical = null;
}
