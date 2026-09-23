<?php

use App\Data\V1\EventData;
use App\Data\V1\EventTypeData;
use App\DataTypeObjects\PageData;
use App\DataTypeObjects\ContentPreview;
use App\Data\Navigation\DesktopNavItemData;
use App\Data\EventTypeData as LocalEventTypeData;
use App\StatamicDTO\Blueprints\Globals\DesktopNav;
use App\DataTypeObjects\API\V1\Content\BlogResponse;
use App\DataTypeObjects\API\V1\Content\ExperienceResponse;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use App\Support\TypeScript\UnionAwareDataTypeScriptTransformer;
use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;
use App\StatamicDTO\Blueprints\Collections\Events\MultidayRetreat;

/**
 * The transformer emits unresolved symbols as `{%Fqcn%}` placeholders, which
 * the writer later rewrites to their dotted TypeScript namespace.
 */
function transformToTypeScript(string $class): string
{
    $transformer = new UnionAwareDataTypeScriptTransformer(
        app(TypeScriptTransformerConfig::class)
    );

    return $transformer->transform(
        new ReflectionClass($class),
        class_basename($class)
    )->transformed;
}

it('keeps every member of a union declared in an array docblock', function () {
    $transformed = transformToTypeScript(DesktopNav::class);

    expect($transformed)->toContain(
        'desktop_nav: Array<{%App\StatamicDTO\Shared\MainNavItemSet%} | {%App\StatamicDTO\Shared\SingleLinkSet%}> | null;'
    );
});

it('keeps the full page block union rather than collapsing to the first member', function () {
    $transformed = transformToTypeScript(PageData::class);

    preg_match('/blocks: Array<(.+)> \| null;/', $transformed, $matches);

    expect($matches)->not->toBeEmpty();

    expect(explode(' | ', $matches[1]))
        ->toHaveCount(19)
        ->toContain('{%App\StatamicDTO\Shared\HeroSet%}')
        ->toContain('{%App\StatamicDTO\Shared\ImageSliderBlockSet%}');
});

it('leaves single type array docblocks untouched', function () {
    $transformed = transformToTypeScript(PageData::class);

    expect($transformed)->toContain('parent: Array<{%App\DataTypeObjects\ParentData%}> | null;');
});

it('preserves camel cased property names on navigation data', function () {
    $transformed = transformToTypeScript(DesktopNavItemData::class);

    expect($transformed)
        ->toContain('dropdownLinks:')
        ->toContain('bottomLinkTitle:')
        ->toContain('featuredMediaItemImage:')
        ->not->toContain('dropdown_links:');
});

it('emits the event landing page as a structured type rather than an untyped array', function (string $class, string $property) {
    $transformed = transformToTypeScript($class);

    expect($transformed)->toContain(
        "{$property}: {%App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse%} | null;"
    );
})->with([
    'event data' => [EventData::class, 'landingPage'],
    'event type data' => [EventTypeData::class, 'landing_page'],
]);

it('emits the event type content fields as typed collections', function (string $class, string $expected) {
    expect(transformToTypeScript($class))->toContain($expected);
})->with([
    'guide pages' => [LocalEventTypeData::class, 'event_guide_pages: Array<{%App\Data\EventType\GuidePageData%}> | null;'],
    'schedule' => [LocalEventTypeData::class, 'event_schedule: Array<{%App\Data\EventType\ScheduleBlockData%}> | null;'],
    'faqs' => [LocalEventTypeData::class, 'event_faqs: Array<{%App\Data\EventType\FaqData%}> | null;'],
    'photos' => [LocalEventTypeData::class, 'photos: Array<string> | null;'],
    'key details' => [LocalEventTypeData::class, 'key_details: Array<{%App\Data\EventType\KeyDetailData%}> | null;'],
    'action buttons' => [LocalEventTypeData::class, 'action_buttons: Array<{%App\Data\EventType\ActionButtonData%}> | null;'],
    'v1 key details' => [EventTypeData::class, 'key_details: Array<{%App\Data\EventType\KeyDetailData%}> | null;'],
    'v1 faqs' => [EventTypeData::class, 'event_faqs: Array<{%App\Data\EventType\FaqData%}> | null;'],
    'v1 schedule' => [EventTypeData::class, 'event_schedule: Array<{%App\Data\EventType\ScheduleBlockData%}> | null;'],
    'v1 event photos' => [EventData::class, 'eventTypePhotos: Array<string>;'],
]);

it('emits the landing page references as structured types', function (string $expected) {
    expect(transformToTypeScript(EventsShowResponse::class))->toContain($expected);
})->with([
    'event logo' => 'event_logo: {%App\StatamicDTO\Shared\AssetReference%} | null;',
    'event type term' => 'event_type: {%App\StatamicDTO\Shared\TermReference%} | null;',
    'topics' => 'topics: Array<{%App\StatamicDTO\Shared\TermReference%}> | null;',
    'buttons' => 'buttons: Array<{%App\DataTypeObjects\API\V1\Content\Events\LandingPageButton%} | {%App\StatamicDTO\Shared\AddToCalendarSet%}> | null;',
    'images' => 'images: {%App\StatamicDTO\Shared\ImagesGroup%};',
    'hero variant' => 'hero_variant: {%App\DataTypeObjects\StatamicValue%} | null;',
    'related event type' => 'related_event_type: {%App\DataTypeObjects\API\V1\Content\Events\EventTypeReference%} | null;',
    'featured image paths' => 'featured_image: Array<string> | null;',
]);

it('emits the experience response with the shapes the builder produces', function (string $expected) {
    expect(transformToTypeScript(ExperienceResponse::class))->toContain($expected);
})->with([
    'description' => 'description: string | null;',
    'images' => 'images: {%App\\DataTypeObjects\\StatamicAssetImageSet%} | null;',
    'sessions' => 'sessions: Array<string> | null;',
    'experience logo' => 'experience_logo: {%App\\DataTypeObjects\\StatamicAssetData%} | null;',
    'featured fallback' => 'featured_video_fallback_image: {%App\\DataTypeObjects\\StatamicAssetData%} | null;',
    'background fallback' => 'background_video_fallback_image: {%App\\DataTypeObjects\\StatamicAssetData%} | null;',
    'session data' => 'sessionData: Array<Record<string, unknown>> | null;',
]);

it('emits explicit record and reference types instead of any', function (string $class, string $expected) {
    expect(transformToTypeScript($class))->toContain($expected);
})->with([
    'new content' => [LocalEventTypeData::class, 'new_content: Array<Record<string, unknown>> | null;'],
    'preview related content' => [ContentPreview::class, 'related_content: Array<Record<string, unknown>> | null;'],
    'preview collection' => [ContentPreview::class, 'collection: {%App\\StatamicDTO\\Shared\\CollectionReference%} | null;'],
    'preview blueprint' => [ContentPreview::class, 'blueprint: {%App\\DataTypeObjects\\Blueprint%} | null;'],
    'blog content' => [BlogResponse::class, 'content: string | null;'],
    'blog image' => [BlogResponse::class, 'featured_image_src: string | null;'],
    'generated asset' => [MultidayRetreat::class, 'event_logo: {%App\\StatamicDTO\\Shared\\AssetReference%} | null;'],
    'generated term' => [MultidayRetreat::class, 'event_type: {%App\\StatamicDTO\\Shared\\TermReference%} | null;'],
    'generated topics' => [MultidayRetreat::class, 'topics: Array<{%App\\StatamicDTO\\Shared\\TermReference%}> | null;'],
    'generated collection' => [MultidayRetreat::class, 'collection: {%App\\StatamicDTO\\Shared\\CollectionReference%} | null;'],
    'generated belongs_to' => [MultidayRetreat::class, 'related_event_type: Record<string, unknown> | null;'],
]);
