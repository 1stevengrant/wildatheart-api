<?php

use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use Statamic\Facades\Term;
use App\Data\V1\EventTypeData;
use Statamic\Facades\Taxonomy;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Bus::fake();
    Mail::fake();

    if (! Taxonomy::handleExists('topics')) {
        Taxonomy::make('topics')->save();
    }

    Term::make('for-men')->taxonomy('topics')->data(['title' => 'For Men'])->save();
});

it('serves the event page content from the event type', function () {
    $eventType = EventType::factory()->create([
        'page_logo' => 'files/events/logo.svg',
        'page_background_video_url' => 'https://player.vimeo.com/background.mp4',
        'page_background_image' => 'files/events/background.jpg',
        'page_topics' => ['for-men', 'topics::for-men', 'no-longer-a-topic'],
        'page_leader_text' => 'Fires',
        'page_hero_title' => [['text' => 'Just Men.', 'text_style' => 'solid']],
        'page_hero_subtitle' => 'A monthly gathering around a fire.',
        'page_featured_video_url' => 'https://player.vimeo.com/video/1',
        'page_featured_video_fallback_image' => 'files/events/featured.jpg',
        'page_description' => '<p>The body copy.</p>',
        'key_details' => [['id' => 'detail-1', 'icon' => 'fire', 'headline' => 'Monthly', 'description' => null]],
    ]);

    $event = Event::factory()->create([
        'user_id' => User::factory()->create()->id,
        'event_type_id' => $eventType->id,
    ]);

    $this->getJson(route('v1.events.events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('eventTypePage', [
            'logo' => 's3::files/events/logo.svg',
            'backgroundVideoUrl' => 'https://player.vimeo.com/background.mp4',
            'backgroundImage' => 's3::files/events/background.jpg',
            'topics' => [
                ['slug' => 'for-men', 'title' => 'For Men'],
                ['slug' => 'for-men', 'title' => 'For Men'],
            ],
            'leaderText' => 'Fires',
            'heroTitle' => [['text' => 'Just Men.', 'text_style' => 'solid']],
            'heroSubtitle' => 'A monthly gathering around a fire.',
            'featuredVideoUrl' => 'https://player.vimeo.com/video/1',
            'featuredVideoFallbackImage' => 's3::files/events/featured.jpg',
            'description' => '<p>The body copy.</p>',
            'keyDetails' => [['id' => 'detail-1', 'icon' => 'fire', 'headline' => 'Monthly', 'description' => null]],
        ])
        ->assertJsonPath('eventTypeDescription', 'The body copy.');
});

it('describes the event type with the V1 description as plain text, not the Local Platform description', function () {
    $eventType = EventType::factory()->create([
        'description' => '## Local Platform copy',
        'page_description' => '<h3>Find a retreat:</h3><p>Men&#039;s retreat.</p><ul><li>Four days</li></ul>',
    ]);

    expect(EventTypeData::fromEventType($eventType)->description)
        ->toBe("Find a retreat: Men's retreat. Four days");
});

it('has no V1 description when none has been entered', function () {
    $eventType = EventType::factory()->create(['description' => 'Local Platform copy', 'page_description' => null]);

    expect(EventTypeData::fromEventType($eventType)->description)->toBeNull();
});

it('serves empty event page content when nothing has been entered', function () {
    $event = Event::factory()->create([
        'user_id' => User::factory()->create()->id,
        'event_type_id' => EventType::factory()->create(['key_details' => null])->id,
    ]);

    $this->getJson(route('v1.events.events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('eventTypePage.logo', null)
        ->assertJsonPath('eventTypePage.topics', [])
        ->assertJsonPath('eventTypePage.heroTitle', [])
        ->assertJsonPath('eventTypePage.keyDetails', []);
});
