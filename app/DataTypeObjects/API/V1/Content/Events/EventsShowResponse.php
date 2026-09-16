<?php

namespace App\DataTypeObjects\API\V1\Content\Events;

use App\Casts\PassthroughCast;
use App\DataTypeObjects\TextBlock;
use App\StatamicDTO\Shared\HeroSet;
use App\StatamicDTO\Shared\IconSet;
use App\StatamicDTO\Shared\HeaderSet;
use App\DataTypeObjects\StatamicValue;
use App\DataTypeObjects\TextLineBlock;
use App\StatamicDTO\Shared\ButtonsSet;
use App\StatamicDTO\Shared\HeroLogoSet;
use App\StatamicDTO\Shared\ImagesGroup;
use App\StatamicDTO\Shared\CodeBlockSet;
use App\StatamicDTO\Shared\ContainerSet;
use App\StatamicDTO\Shared\CountdownSet;
use App\StatamicDTO\Shared\HeroTitleSet;
use App\StatamicDTO\Shared\BentoBlockSet;
use App\StatamicDTO\Shared\HeroSliderSet;
use App\StatamicDTO\Shared\StyledTextSet;
use App\StatamicDTO\Shared\TermReference;
use App\StatamicDTO\Shared\VideoBlockSet;
use App\StatamicDTO\Shared\AssetReference;
use App\StatamicDTO\Shared\MediaSliderSet;
use App\StatamicDTO\Shared\TestimonialSet;
use App\StatamicDTO\Shared\ColumnsBlockSet;
use App\StatamicDTO\Shared\DividerBlockSet;
use Spatie\LaravelData\Attributes\WithCast;
use App\StatamicDTO\Shared\AddToCalendarSet;
use App\StatamicDTO\Shared\SubscribeFormSet;
use App\DataTypeObjects\API\V1\EntryResponse;
use App\StatamicDTO\Shared\ImageSliderBlockSet;
use App\StatamicDTO\Shared\TestimonialsBlockSet;
use Spatie\LaravelData\Attributes\WithTransformer;
use App\StatamicDTO\Shared\VerticalVideosBlockGroup;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;

#[TypeScript]
class EventsShowResponse extends EntryResponse
{
    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet>|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $blocks;

    public ?string $description;

    /** @var array<LandingPageButton|AddToCalendarSet>|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $buttons;

    #[TypeScriptType('\\' . AssetReference::class . '|null')]
    public ?array $event_logo;

    #[TypeScriptType('\\' . TermReference::class . '|null')]
    public ?array $event_type;

    /** @var string[]|null Asset paths in the `app` container */
    public ?array $featured_image;

    #[TypeScriptType('\\' . AssetReference::class . '|null')]
    public ?array $featured_video_fallback_image;

    public ?string $featured_video_url;

    /** @var TermReference[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $topics;

    public ?string $background_video_url;

    #[TypeScriptType('\\' . AssetReference::class . '|null')]
    public ?array $background_video_fallback_image;

    #[TypeScriptType('\\' . StatamicValue::class . '|null')]
    public ?array $height;

    #[TypeScriptType('\\' . AssetReference::class . '|null')]
    public ?array $hero_logo;

    /** @var TextBlock[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $hero_subtitle;

    /** @var TextLineBlock[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $hero_title;

    #[TypeScriptType('\\' . StatamicValue::class . '|null')]
    public ?array $hero_variant;

    public bool $hide_from_search_engines;

    #[TypeScriptType('\\' . ImagesGroup::class)]
    public array $images;

    public ?bool $is_local_event;

    public ?string $leader_text;

    #[TypeScriptType('\\' . StatamicValue::class . '|null')]
    public ?array $position;

    #[TypeScriptType('\\' . EventTypeReference::class . '|null')]
    public ?array $related_event_type;

    public ?string $seo_description;

    #[TypeScriptType('\\' . AssetReference::class . '|null')]
    public ?array $seo_image;

    public ?string $seo_title;

    public ?bool $show_in_collections;

    public ?string $site_url;

    public ?string $sitemap_priority;

    public ?string $subtitle;

    /** @var TestimonialSet[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $testimonials;

    public ?string $wide;

    #[TypeScriptType('\\' . StatamicValue::class . '|null')]
    public ?array $width;

    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet>|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $final_details_blocks;

    #[TypeScriptType('\\' . VerticalVideosBlockGroup::class . '|null')]
    public ?array $vertical_videos_block;

    /** @var IconSet[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $icons;
}
