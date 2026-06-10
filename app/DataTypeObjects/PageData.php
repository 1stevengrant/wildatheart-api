<?php

namespace App\DataTypeObjects;

use App\Enums\ContentStatus;
use Spatie\LaravelData\Data;
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
use App\StatamicDTO\Shared\ImageSliderBlockSet;
use App\StatamicDTO\Shared\TestimonialsBlockSet;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class PageData extends Data
{
    public string $id;

    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet>|null */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public ?array $blocks;

    public ImageData $images;

    public string $locale = 'default';

    /** @var ParentData[] */
    public ?array $parent = null;

    public bool $private = false;

    public bool $published = true;

    public ?string $seo_description = null;

    #[LiteralTypeScriptType('Array<Record<string, unknown>> | null')]
    public ?array $seo_image = null;

    public ?string $seo_title = null;

    public string $slug;

    public string $status = ContentStatus::PUBLISHED->value;

    public string $title;

    public ?string $uri;

    public ?string $url;

    public Blueprint $blueprint;
}
