<?php

namespace App\DataTypeObjects\API\V1\Content\Events;

use App\Casts\PassthroughCast;
use App\StatamicDTO\Shared\HeroSet;
use App\StatamicDTO\Shared\HeaderSet;
use App\StatamicDTO\Shared\ButtonsSet;
use App\StatamicDTO\Shared\HeroLogoSet;
use App\StatamicDTO\Shared\CodeBlockSet;
use App\StatamicDTO\Shared\ContainerSet;
use App\StatamicDTO\Shared\CountdownSet;
use App\StatamicDTO\Shared\HeroTitleSet;
use App\StatamicDTO\Shared\BentoBlockSet;
use App\StatamicDTO\Shared\HeroSliderSet;
use App\StatamicDTO\Shared\StyledTextSet;
use App\StatamicDTO\Shared\VideoBlockSet;
use App\StatamicDTO\Shared\MediaSliderSet;
use App\StatamicDTO\Shared\ColumnsBlockSet;
use App\StatamicDTO\Shared\DividerBlockSet;
use Spatie\LaravelData\Attributes\WithCast;
use App\StatamicDTO\Shared\AddToCalendarSet;
use App\StatamicDTO\Shared\SubscribeFormSet;
use App\DataTypeObjects\API\V1\EntryResponse;
use App\StatamicDTO\Shared\ImageSliderBlockSet;
use App\StatamicDTO\Shared\TestimonialsBlockSet;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventsShowResponse extends EntryResponse
{
    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet>|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
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

    public ?array $final_details_blocks;

    public ?array $vertical_videos_block;

    public ?array $icons;
}
