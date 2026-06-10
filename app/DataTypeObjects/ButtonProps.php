<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ButtonProps extends Data
{
    public ?string $text;

    public ?string $href;

    public ?string $variant;

    public ?IconProps $leftIcon;

    public ?IconProps $rightIcon;

    public ?bool $openInModal;

    public ?bool $openInNewTab;
}

#[TypeScript]
class IconProps extends Data
{
    public ?string $name;

    public ?string $width;

    public ?string $height;
}
