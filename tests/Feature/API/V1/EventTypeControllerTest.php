<?php

use App\Models\EventType;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use App\Services\EventResponseBuilder;

beforeEach(function () {
    EventType::truncate();
    cache()->forget('eventTypesWithCounts');

    if (! Collection::handleExists('pages')) {
        Collection::make('pages')->save();
    }
});

afterEach(function () {
    EventType::truncate();
    cache()->forget('eventTypesWithCounts');
});

it('includes related landing page data on event types', function () {
    /** @var Statamic\Entries\Entry $landingPage */
    $landingPage = Entry::make();

    $landingPage
        ->collection('pages')
        ->blueprint('page')
        ->slug('event-type-landing-page')
        ->published(true)
        ->data([
            'title' => 'Event Type Landing Page',
        ]);

    $landingPage->save();

    EventType::factory()->create([
        'name' => 'Wild at Heart Retreat',
        'related_landing_page_id' => $landingPage->id(),
    ]);

    $this->mock(EventResponseBuilder::class)
        ->shouldReceive('build')
        ->once()
        ->andReturn([
            'id' => $landingPage->id(),
            'title' => 'Event Type Landing Page',
            'slug' => 'event-type-landing-page',
        ]);

    $response = $this->getJson('/api/v1/events/types');

    $response->assertSuccessful()
        ->assertJsonPath('0.name', 'Wild at Heart Retreat')
        ->assertJsonPath('0.landing_page.title', 'Event Type Landing Page')
        ->assertJsonPath('0.landing_page.slug', 'event-type-landing-page');
});

it('returns null landing page when no related page is configured', function () {
    EventType::factory()->create([
        'name' => 'Fires',
        'related_landing_page_id' => null,
    ]);

    $response = $this->getJson('/api/v1/events/types');

    $response->assertSuccessful()
        ->assertJsonPath('0.name', 'Fires')
        ->assertJsonPath('0.landing_page', null);
});

it('includes date selector metadata for fire event types', function () {
    EventType::factory()->create([
        'name' => EventType::FIRES,
        'group_type' => EventType::TYPE_FIRE,
    ]);

    $response = $this->getJson('/api/v1/events/types');

    $response->assertSuccessful()
        ->assertJsonPath('0.date_selector_kind', 'recurring')
        ->assertJsonPath('0.fixed_duration_days', null)
        ->assertJsonPath('0.requires_frequency', true);
});

it('includes one-day duration metadata for one-day captivating retreat', function () {
    EventType::factory()->create([
        'name' => EventType::CAPTIVATING_CORE_RETREAT_1_DAY,
        'group_type' => EventType::TYPE_RETREAT,
    ]);

    $response = $this->getJson('/api/v1/events/types');

    $response->assertSuccessful()
        ->assertJsonPath('0.date_selector_kind', 'fixed_duration_start_date')
        ->assertJsonPath('0.fixed_duration_days', 1)
        ->assertJsonPath('0.requires_frequency', false);
});
