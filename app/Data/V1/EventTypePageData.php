<?php

namespace App\Data\V1;

use App\Models\EventType;
use Statamic\Facades\Term;
use Spatie\LaravelData\Data;
use App\Data\EventType\TopicData;
use App\Data\EventType\KeyDetailData;
use App\Data\EventType\HeroTitleLineData;
use App\Data\Concerns\CollectsNullableData;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The content of an event detail page, edited on the event type's "Event Page" tab.
 * Assets are Statamic asset ids in the `s3` container, e.g. `s3::files/events/logo.png`.
 */
#[TypeScript]
class EventTypePageData extends Data
{
    use CollectsNullableData;

    private const string ASSET_CONTAINER = 's3';

    public function __construct(
        public ?string $logo,
        public ?string $backgroundVideoUrl,
        public ?string $backgroundImage,
        /** @var TopicData[] */
        public array $topics,
        public ?string $leaderText,
        /** @var HeroTitleLineData[] */
        public array $heroTitle,
        public ?string $heroSubtitle,
        public ?string $featuredVideoUrl,
        public ?string $featuredVideoFallbackImage,
        public ?string $description,
        /** @var KeyDetailData[] */
        public array $keyDetails,
    ) {}

    public static function fromEventType(EventType $eventType): self
    {
        return new self(
            logo: self::assetId($eventType->page_logo),
            backgroundVideoUrl: $eventType->page_background_video_url,
            backgroundImage: self::assetId($eventType->page_background_image),
            topics: self::topics($eventType->page_topics ?? []),
            leaderText: $eventType->page_leader_text,
            heroTitle: self::collectOrNull(HeroTitleLineData::class, $eventType->page_hero_title) ?? [],
            heroSubtitle: $eventType->page_hero_subtitle,
            featuredVideoUrl: $eventType->page_featured_video_url,
            featuredVideoFallbackImage: self::assetId($eventType->page_featured_video_fallback_image),
            description: $eventType->page_description,
            keyDetails: self::collectOrNull(KeyDetailData::class, $eventType->key_details) ?? [],
        );
    }

    private static function assetId(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return self::ASSET_CONTAINER . '::' . $path;
    }

    /**
     * @param  array<int, string>  $slugs
     * @return TopicData[]
     */
    private static function topics(array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug) => Term::find('topics::' . str($slug)->afterLast('::')))
            ->filter()
            ->map(fn ($term) => new TopicData(slug: $term->slug(), title: $term->title()))
            ->values()
            ->all();
    }
}
