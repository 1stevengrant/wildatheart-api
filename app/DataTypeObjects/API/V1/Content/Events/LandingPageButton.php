<?php

namespace App\DataTypeObjects\API\V1\Content\Events;

use Spatie\LaravelData\Data;
use App\DataTypeObjects\StatamicValue;
use App\StatamicDTO\Shared\LeftIconGroup;
use App\StatamicDTO\Shared\AssetReference;
use App\StatamicDTO\Shared\RightIconGroup;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A `button` set in a landing page `buttons` replicator as augmented by Statamic:
 * select fields arrive as {value, label, key} objects and the link as a plain URL.
 */
#[TypeScript]
class LandingPageButton extends Data
{
    public string $id;

    public string $type = 'button';

    public ?string $text = null;

    public ?AssetReference $image = null;

    public ?string $href = null;

    public ?StatamicValue $variant = null;

    public ?StatamicValue $size = null;

    public ?LeftIconGroup $left_icon = null;

    public ?RightIconGroup $right_icon = null;

    public ?bool $open_in_modal = null;

    public ?bool $open_in_new_tab = null;
}
