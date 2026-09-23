<?php

use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;

/**
 * The typed landing page properties are documentation for the frontends. They
 * must not alter the augmented Statamic payload on its way through the API.
 */
function augmentedLandingPage(): array
{
    $asset = ['id' => 'app::events/logo.png', 'url' => '/assets/events/logo.png', 'permalink' => 'https://example.test/assets/events/logo.png', 'api_url' => 'https://example.test/api/assets/app/events/logo.png', 'alt' => null];

    return [
        'id' => 'entry-1',
        'title' => 'Wild at Heart Retreat',
        'slug' => 'wild-at-heart-retreat',
        'uri' => '/events/wild-at-heart-retreat',
        'url' => '/events/wild-at-heart-retreat',
        'blueprint' => ['title' => 'Event Landing Page', 'handle' => 'multiday_retreat'],
        'blocks' => [['type' => 'hero_title', 'hero_title' => [['type' => 'text_line', 'text' => 'Wild', 'text_style' => ['value' => 'solid', 'label' => 'Solid', 'key' => 'solid'], 'id' => 'tl1']], 'id' => 'blk1']],
        'description' => null,
        'buttons' => [['id' => 'btn1', 'type' => 'button', 'text' => 'Register', 'image' => null, 'href' => 'https://example.test/register', 'variant' => ['value' => 'primary', 'label' => 'Primary', 'key' => 'primary'], 'size' => ['value' => '1', 'label' => 'Medium', 'key' => '1'], 'left_icon' => ['icon' => null, 'width' => null, 'height' => null], 'right_icon' => ['icon' => 'arrow-right', 'width' => '16', 'height' => '16'], 'open_in_modal' => false, 'open_in_new_tab' => true]],
        'event_logo' => $asset,
        'event_type' => ['id' => 'term-1', 'title' => 'Retreats', 'slug' => 'retreats', 'url' => '/event-types/retreats', 'permalink' => 'https://example.test/event-types/retreats', 'api_url' => 'https://example.test/api/terms/retreats'],
        'featured_image' => ['events/featured.jpg'],
        'featured_video_fallback_image' => null,
        'featured_video_url' => 'https://vimeo.com/1',
        'topics' => [['id' => 'topic-1', 'title' => 'Men', 'slug' => 'men', 'url' => '/topics/men', 'permalink' => 'https://example.test/topics/men', 'api_url' => 'https://example.test/api/topics/men']],
        'background_video_url' => 'https://vimeo.com/2',
        'background_video_fallback_image' => $asset,
        'height' => ['value' => '2', 'label' => 'Tall', 'key' => '2'],
        'hero_logo' => null,
        'hero_subtitle' => [['type' => 'text', 'text' => 'Four days']],
        'hero_title' => [['type' => 'text_line', 'text' => 'Wild', 'text_style' => ['value' => 'solid', 'label' => 'Solid', 'key' => 'solid'], 'id' => 'tl1']],
        'hero_variant' => ['value' => 'center', 'label' => 'Center', 'key' => 'center'],
        'hide_from_search_engines' => false,
        'images' => ['wide' => $asset, 'square' => null, 'vertical' => null],
        'is_local_event' => true,
        'leader_text' => null,
        'position' => ['value' => 'start', 'label' => 'Start', 'key' => 'start'],
        'related_event_type' => ['id' => 2, 'name' => 'Wild at Heart Retreat', 'api_url' => 'https://example.test/api/event-types/2'],
        'seo_description' => null,
        'seo_image' => null,
        'seo_title' => null,
        'show_in_collections' => true,
        'site_url' => 'https://example.test',
        'sitemap_priority' => '0.5',
        'subtitle' => null,
        'testimonials' => [['body' => 'Life changing.', 'name' => 'John', 'id' => 't1', 'type' => 'testimonial']],
        'wide' => null,
        'width' => ['value' => '1', 'label' => 'Wide', 'key' => '1'],
        'final_details_blocks' => [['type' => 'text', 'text' => 'Bring a jacket']],
        'vertical_videos_block' => ['title' => 'Stories', 'videos' => [['video_url' => 'https://vimeo.com/3', 'poster_url' => null, 'title' => 'Story', 'subtitle' => null, 'id' => 'v1', 'type' => 'video']]],
        'icons' => [['type' => 'icon', 'id' => 'i1', 'enabled' => true, 'icon' => 'calendar', 'width' => null, 'height' => null, 'title' => 'Four days', 'subtitle' => null]],
    ];
}

it('passes the augmented landing page through unchanged', function () {
    $payload = augmentedLandingPage();

    $transformed = EventsShowResponse::from($payload)->toArray();

    foreach ($payload as $key => $value) {
        expect($transformed[$key])->toBe($value, "Key `{$key}` was altered by the typed response");
    }
});

it('keeps typed reference fields as plain arrays rather than data objects', function () {
    $response = EventsShowResponse::from(augmentedLandingPage());

    expect($response->buttons[0])->toBeArray()
        ->and($response->topics[0])->toBeArray()
        ->and($response->hero_title[0])->toBeArray()
        ->and($response->event_logo)->toBeArray()
        ->and($response->images)->toBeArray();
});
