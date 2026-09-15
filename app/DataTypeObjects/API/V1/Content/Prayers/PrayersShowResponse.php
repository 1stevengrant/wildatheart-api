<?php

namespace App\DataTypeObjects\API\V1\Content\Prayers;

use App\DataTypeObjects\File;
use App\Casts\PassthroughCast;
use App\StatamicDTO\Shared\HeroSet;
use App\DataTypeObjects\PlayableFile;
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
class PrayersShowResponse extends EntryResponse
{
    public string $content;

    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet> */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public array $before_content_blocks;

    /** @var array<HeroSet|HeroSliderSet|MediaSliderSet|CountdownSet|ButtonsSet|HeroTitleSet|HeroLogoSet|SubscribeFormSet|AddToCalendarSet|HeaderSet|ContainerSet|StyledTextSet|ColumnsBlockSet|BentoBlockSet|DividerBlockSet|VideoBlockSet|CodeBlockSet|TestimonialsBlockSet|ImageSliderBlockSet> */
    #[WithCast(PassthroughCast::class)]
    #[WithTransformer(PassthroughCast::class)]
    public array $after_content_blocks;

    public string $summary;

    public string $drupal_url;

    public ?PlayableFile $mp3;

    public ?File $pdf;
}
