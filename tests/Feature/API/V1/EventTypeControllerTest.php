<?php

use App\Models\EventType;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use App\Services\EventResponseBuilder;

beforeEach(function () {
    EventType::truncate();
    cache()->forget('eventTypesWithCounts.serialised.v3');

    if (! Collection::handleExists('pages')) {
        Collection::make('pages')->save();
    }
});

afterEach(function () {
    EventType::truncate();
    cache()->forget('eventTypesWithCounts.serialised.v3');
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

it('does not require a frequency for courses', function () {
    EventType::factory()->create([
        'name' => EventType::WAH_EXPERIENCE,
        'group_type' => EventType::TYPE_EXPERIENCE,
    ]);

    $this->getJson('/api/v1/events/types')
        ->assertSuccessful()
        ->assertJsonPath('0.requires_frequency', false);
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

it('serialises the landing page once and reuses it across requests', function () {
    /** @var Statamic\Entries\Entry $landingPage */
    $landingPage = Entry::make();

    $landingPage
        ->collection('pages')
        ->blueprint('page')
        ->slug('cached-landing-page')
        ->published(true)
        ->data(['title' => 'Cached Landing Page']);

    $landingPage->save();

    EventType::factory()->create([
        'name' => 'Wild at Heart Retreat',
        'related_landing_page_id' => $landingPage->id(),
    ]);

    // The expensive part of this endpoint is resolving each event type's
    // Statamic landing page. It must happen while priming the cache, not on
    // every request.
    $this->mock(EventResponseBuilder::class)
        ->shouldReceive('build')
        ->once()
        ->andReturn(['title' => 'Cached Landing Page']);

    $this->getJson('/api/v1/events/types')->assertSuccessful();
    $this->getJson('/api/v1/events/types')->assertSuccessful();
    $this->getJson('/api/v1/events/types')->assertSuccessful();
});

it('returns only the requested fields', function () {
    EventType::factory()->create(['name' => 'Fires', 'related_landing_page_id' => null]);

    $response = $this->getJson('/api/v1/events/types?fields=id,camel_case_name');

    $response->assertSuccessful();

    expect(array_keys($response->json('0')))->toBe(['id', 'camel_case_name']);
});

it('returns every field when fields is absent or empty', function () {
    EventType::factory()->create(['name' => 'Fires', 'related_landing_page_id' => null]);

    $full = $this->getJson('/api/v1/events/types')->json('0');
    $empty = $this->getJson('/api/v1/events/types?fields=')->json('0');

    expect($empty)->toBe($full)
        ->and(array_keys($full))->toContain('landing_page', 'key_details', 'camel_case_name');
});

it('rejects an unknown field with a 422', function () {
    EventType::factory()->create(['name' => 'Fires', 'related_landing_page_id' => null]);

    $this->getJson('/api/v1/events/types?fields=id,not_a_field')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fields']);
});

it('allows shared caches to reuse the event types response', function () {
    EventType::factory()->create(['name' => 'Fires', 'related_landing_page_id' => null]);

    $cacheControl = $this->getJson('/api/v1/events/types')->headers->get('cache-control');

    expect($cacheControl)->toContain('public')
        ->and($cacheControl)->toContain('max-age=300')
        ->and($cacheControl)->not->toContain('no-cache')
        ->and($cacheControl)->not->toContain('private');
});

it('rebuilds the cached payload when a related landing page entry is saved', function () {
    /** @var Statamic\Entries\Entry $landingPage */
    $landingPage = Entry::make();

    $landingPage
        ->collection('pages')
        ->blueprint('page')
        ->slug('invalidated-landing-page')
        ->published(true)
        ->data(['title' => 'Before']);

    $landingPage->save();

    EventType::factory()->create([
        'name' => 'Wild at Heart Retreat',
        'related_landing_page_id' => $landingPage->id(),
    ]);

    $this->getJson('/api/v1/events/types')
        ->assertSuccessful()
        ->assertJsonPath('0.landing_page.title', 'Before');

    $landingPage->data(['title' => 'After'])->save();

    $this->getJson('/api/v1/events/types')
        ->assertSuccessful()
        ->assertJsonPath('0.landing_page.title', 'After');
});
