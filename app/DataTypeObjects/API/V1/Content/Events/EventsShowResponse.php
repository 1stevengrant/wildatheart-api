<?php

namespace App\DataTypeObjects\API\V1\Content\Events;

use App\DataTypeObjects\API\V1\EntryResponse;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventsShowResponse extends EntryResponse
{
    public ?array $blocks;

    public ?string $description;

    public ?array $buttons;

    public ?array $event_logo;

    public ?array $event_type;

    public ?array $featured_image;

    public ?array $featured_video_fallback_image;

    public ?string $featured_video_url;

    public ?array $topics;

    public ?string $background_video_url;

    public ?array $background_video_fallback_image;

    public ?array $height;

    public ?array $hero_logo;

    public ?array $hero_subtitle;

    public ?array $hero_title;

    public ?array $hero_variant;

    public bool $hide_from_search_engines;

    public array $images;

    public ?bool $is_local_event;

    public ?string $leader_text;

    public ?array $position;

    public ?array $related_event_type;

    public ?string $seo_description;

    public ?array $seo_image;

    public ?string $seo_title;

    public ?bool $show_in_collections;

    public ?string $site_url;

    public ?string $sitemap_priority;

    public ?string $subtitle;

    public ?array $testimonials;

    public ?string $wide;

    public ?array $width;
}
