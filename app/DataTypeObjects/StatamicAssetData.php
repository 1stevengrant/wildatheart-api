<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

/**
 * A fully augmented Statamic asset, as produced by Entry::toArray() without shallow nesting.
 * `url` and `permalink` are null when the file is missing from the container.
 */
#[TypeScript]
class StatamicAssetData extends Data
{
    public string $id;

    public string $title;

    public string $path;

    public string $filename;

    public string $basename;

    public string $extension;

    public bool $is_asset;

    public bool $is_audio;

    public bool $is_previewable;

    public bool $is_image;

    public bool $is_svg;

    public bool $is_video;

    public bool $is_pdf;

    /** @var array<string, mixed> Augmented asset blueprint */
    #[LiteralTypeScriptType('Record<string, unknown>')]
    public array $blueprint;

    public string $edit_url;

    /** @var array<string, mixed> Augmented asset container */
    #[LiteralTypeScriptType('Record<string, unknown>')]
    public array $container;

    public string $folder;

    public ?string $url = null;

    public ?string $permalink = null;

    public string $api_url;

    public string $size;

    public int $size_bytes;

    public float $size_kilobytes;

    public float $size_megabytes;

    public float $size_gigabytes;

    public int $size_b;

    public float $size_kb;

    public float $size_mb;

    public float $size_gb;

    /** @var string ISO 8601 date/time */
    public string $last_modified;

    public int $last_modified_timestamp;

    /** @var string ISO 8601 date/time */
    public string $last_modified_instance;

    public string $focus;

    public bool $has_focus;

    public string $focus_css;

    public ?int $height = null;

    public ?int $width = null;

    public ?string $orientation = null;

    public ?float $ratio = null;

    public ?string $mime_type = null;

    public ?float $duration = null;

    public ?float $duration_seconds = null;

    public ?float $duration_minutes = null;

    public ?float $duration_sec = null;

    public ?float $duration_min = null;

    public ?string $playtime = null;

    public ?string $alt = null;

    /** @var string[]|null */
    public ?array $color_palette = null;

    /** @var mixed Value of the custom ShowCdnUrl fieldtype on the asset blueprint */
    #[LiteralTypeScriptType('unknown')]
    public mixed $show_cdn_url_field;
}
