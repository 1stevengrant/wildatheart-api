<?php

use App\Data\V1\EventData;
use App\Data\V1\EventTypeData;
use App\DataTypeObjects\PageData;
use App\Data\Navigation\DesktopNavItemData;
use App\StatamicDTO\Blueprints\Globals\DesktopNav;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use App\Support\TypeScript\UnionAwareDataTypeScriptTransformer;

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
