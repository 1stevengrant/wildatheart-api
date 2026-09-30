<?php

use App\Models\EventType;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use App\Services\EventResponseBuilder;
use App\Actions\EventType\CopyLandingPageContentToEventType;

beforeEach(function () {
    if (! Collection::handleExists('events')) {
        Collection::make('events')->save();
    }
});

function eventTypeWithLandingPage(array $attributes = []): EventType
{
    $entry = Entry::make()
        ->collection('events')
        ->blueprint('multiday_retreat')
        ->slug('landing-page-' . uniqid())
        ->published(true);

    $entry->data(['title' => 'Landing Page']);
    $entry->save();

    return EventType::factory()->create([
        'related_landing_page_id' => $entry->id(),
        ...$attributes,
    ]);
}

function landingPageBuiltAs(array $response): void
{
    $builder = Mockery::mock(EventResponseBuilder::class);
    $builder->shouldReceive('build')->andReturn($response);

    app()->instance(EventResponseBuilder::class, $builder);
}

it('copies the landing page content onto the event type', function () {
    landingPageBuiltAs([
        'slug' => 'wild-at-heart-retreat',
        'title' => 'Wild at Heart Retreat',
        'event_logo' => ['id' => 's3::files/events/logo.svg'],
        'background_video_url' => 'https://player.vimeo.com/background.mp4',
        'background_video_fallback_image' => ['id' => 's3::files/events/background.jpg'],
        'topics' => [['slug' => 'for-men', 'title' => 'For Men'], ['slug' => 'beginner-friendly', 'title' => 'Beginner Friendly']],
        'blocks' => [
            [
                'type' => 'hero_title',
                'hero_title' => [
                    ['text' => 'Recover Your', 'text_style' => ['value' => 'hollow', 'label' => 'Hero 1 Hollow']],
                    ['text' => 'Wild Heart.', 'text_style' => ['value' => 'solid', 'label' => 'Hero 1 Solid']],
                ],
            ],
            ['type' => 'styled_text', 'text' => '<p>A monthly gathering around a fire.</p>'],
            ['type' => 'buttons', 'buttons' => []],
            null,
        ],
        'featured_video_url' => 'https://player.vimeo.com/video/1',
        'featured_video_fallback_image' => ['id' => 's3::files/events/featured.jpg'],
        'description' => '<p>The body copy.</p>',
    ]);

    $eventType = eventTypeWithLandingPage();

    CopyLandingPageContentToEventType::run($eventType);

    expect($eventType->fresh())
        ->page_logo->toBe('files/events/logo.svg')
        ->page_background_video_url->toBe('https://player.vimeo.com/background.mp4')
        ->page_background_image->toBe('files/events/background.jpg')
        ->page_topics->toBe(['for-men', 'beginner-friendly'])
        ->page_leader_text->toBe('Wild at Heart Retreat')
        ->page_hero_title->toBe([
            ['text' => 'Recover Your', 'text_style' => 'hollow'],
            ['text' => 'Wild Heart.', 'text_style' => 'solid'],
        ])
        ->page_hero_subtitle->toBe('A monthly gathering around a fire.')
        ->page_featured_video_url->toBe('https://player.vimeo.com/video/1')
        ->page_featured_video_fallback_image->toBe('files/events/featured.jpg')
        ->page_description->toBe('<p>The body copy.</p>');
});

it('keeps content already edited on the event type', function () {
    landingPageBuiltAs([
        'title' => 'From the landing page',
        'event_logo' => ['id' => 's3::files/events/logo.svg'],
    ]);

    $eventType = eventTypeWithLandingPage(['page_leader_text' => 'Edited on the event type']);

    CopyLandingPageContentToEventType::run($eventType);

    expect($eventType->fresh())
        ->page_leader_text->toBe('Edited on the event type')
        ->page_logo->toBe('files/events/logo.svg');
});

it('borrows the experience video when the landing page has no background video', function () {
    landingPageBuiltAs(['slug' => 'captivating-fires', 'background_video_url' => null]);

    $eventType = eventTypeWithLandingPage();

    CopyLandingPageContentToEventType::run($eventType);

    expect($eventType->fresh()->page_background_video_url)->toContain('503998859');
});

it('leaves empty landing page content empty', function () {
    landingPageBuiltAs(['blocks' => [['type' => 'text', 'text' => '<p></p>']], 'description' => '<p></p>']);

    $eventType = eventTypeWithLandingPage();

    CopyLandingPageContentToEventType::run($eventType);

    expect($eventType->fresh())
        ->page_hero_title->toBeNull()
        ->page_hero_subtitle->toBeNull()
        ->page_description->toBeNull()
        ->page_background_video_url->toBeNull();
});

it('does nothing when the event type has no landing page', function (?string $landingPageId) {
    $builder = Mockery::mock(EventResponseBuilder::class);
    $builder->shouldNotReceive('build');
    app()->instance(EventResponseBuilder::class, $builder);

    $eventType = EventType::factory()->create(['related_landing_page_id' => $landingPageId]);

    CopyLandingPageContentToEventType::run($eventType);

    expect($eventType->fresh()->page_leader_text)->toBeNull();
})->with([
    'no landing page' => null,
    'a deleted landing page' => 'missing-entry-id',
]);
