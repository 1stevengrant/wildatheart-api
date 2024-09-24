<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Statamic\Entries\Entry as EntriesEntry;

#[TypeScript]
class SlideBlock extends Data
{
    public $id = null;
    public $type = 'slide';
    public ?string $title = null;
    public ?string $featured_image_src = null;
    public ?string $tag = null;
    public ?string $leader = null;
    public ?string $description = null;
    public ?bool $trim_description_bool = null;
    public ?string $details = null;
    public ?CardVariant $variant = CardVariant::portrait;
    public ?ButtonProps $left_cta = null;
    public ?ButtonProps $right_cta = null;
    public ?string $href = null;
    public ?bool $is_coming_soon = null;
}

#[TypeScript]
enum CardVariant: string // Change to backed enum
{
    case landscape = 'landscape'; // Assign string values
    case portrait = 'portrait';
    case square = 'square';
}

#[TypeScript]
class Entry extends EntriesEntry
{
    public string $test_prop;
}
