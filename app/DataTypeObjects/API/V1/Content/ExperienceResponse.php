<?php

namespace App\DataTypeObjects\API\V1\Content;

use App\Casts\PassthroughCast;
use App\DataTypeObjects\TextBlock;
use App\StatamicDTO\Shared\HeroSet;
use App\StatamicDTO\Shared\HeaderSet;
use App\DataTypeObjects\TextLineBlock;
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
use App\DataTypeObjects\StatamicAssetData;
use App\StatamicDTO\Shared\MediaSliderSet;
use App\StatamicDTO\Shared\TestimonialSet;
use App\StatamicDTO\Shared\ColumnsBlockSet;
use App\StatamicDTO\Shared\DividerBlockSet;
use Spatie\LaravelData\Attributes\WithCast;
use App\StatamicDTO\Shared\AddToCalendarSet;
use App\StatamicDTO\Shared\SubscribeFormSet;
use App\DataTypeObjects\API\V1\EntryResponse;
use App\DataTypeObjects\StatamicAssetImageSet;
use App\StatamicDTO\Shared\ImageSliderBlockSet;
use App\StatamicDTO\Shared\TestimonialsBlockSet;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

/**
 * Built by ExperienceResponseBuilder from Entry::toArray(), so nested values are
 * fully augmented: assets are complete asset arrays and select fields are flattened
 * to their `value` by the builder.
 */
#[TypeScript]
class ExperienceResponse extends EntryResponse
{
    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet>|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $blocks = null;

    public ?string $height = null;

    #[TypeScriptType('\\' . StatamicAssetImageSet::class . '|null')]
    public ?array $images = null;

    public ?string $position = null;

    /** @var string[]|null Ids of the linked media item entries */
    public ?array $sessions = null;

    public ?string $subtitle = null;

    public ?string $hero_logo = null;

    /** @var TextLineBlock[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $hero_title = null;

    public int $updated_by = 0;

    public ?string $button_link = null;

    public ?string $button_text = null;

    /** HTML content. The builder does not populate it yet, so it is null in every response. */
    public ?string $description = null;

    public ?string $leader_text = null;

    public ?string $hero_variant = null;

    /** @var TestimonialSet[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $testimonials = null;

    /** @var TextBlock[]|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $hero_subtitle = null;

    #[TypeScriptType('\\' . StatamicAssetData::class . '|null')]
    public ?array $experience_logo = null;

    public ?string $find_a_group_link = null;

    public ?string $host_a_group_link = null;

    public ?string $featured_video_url = null;

    public ?string $related_event_type = null;

    public ?string $subsplash_series_id = null;

    public ?string $background_video_url = null;

    #[TypeScriptType('\\' . StatamicAssetData::class . '|null')]
    public ?array $featured_video_fallback_image = null;

    #[TypeScriptType('\\' . StatamicAssetData::class . '|null')]
    public ?array $background_video_fallback_image = null;

    /** @var array<int, array<string, mixed>>|null Each session entry array merged with its Subsplash media item */
    #[LiteralTypeScriptType('Array<Record<string, unknown>> | null')]
    public ?array $sessionData = null;
}
