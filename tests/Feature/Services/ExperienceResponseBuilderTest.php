<?php

use Statamic\Facades\Entry;
use App\Services\ExperienceResponseBuilder;
use App\Services\Subsplash\SubsplashApiClient;

/**
 * Entry::toArray() only expands relationship fields passed to withRelations(), so the
 * experience's `sessions` stay as entry ids. The structured session references live in
 * `sessionData`, which the builder assembles from each session entry and its Subsplash item.
 *
 * Entries are dated in the past: Statamic treats a dated entry whose date equals the query
 * time as not yet published, which made same-second fixtures flaky.
 */
it('keeps sessions as entry ids and expands them into structured session data', function () {
    $session = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->slug('session-one')
        ->date(now()->subDay())
        ->published(true)
        ->data(['title' => 'Session One', 'subsplash_media_item_id' => 'sub-1']);
    $session->save();

    $experience = Entry::make()
        ->collection('experiences')
        ->blueprint('experience')
        ->slug('test-experience')
        ->date(now()->subDay())
        ->published(true)
        ->data([
            'title' => 'Test Experience',
            'subsplash_series_id' => 'series-1',
            'sessions' => [$session->id()],
        ]);
    $experience->save();

    $this->mock(SubsplashApiClient::class)
        ->shouldReceive('getRelatedMediaItems')
        ->with('series-1')
        ->once()
        ->andReturn([['id' => 'sub-1', 'summary' => 'Session one summary']]);

    $response = app(ExperienceResponseBuilder::class)->buildBySlug('test-experience');

    expect($response->sessions)->toBe([$session->id()])
        ->and($response->sessions[0])->toBeString()
        ->and($response->sessionData)->toHaveCount(1)
        ->and($response->sessionData[0])->toMatchArray([
            'sessionNumber' => 1,
            'sessionSlug' => 'session-1',
            'slug' => 'session-one',
            'title' => 'Session One',
            // The Subsplash item is spread last, so its id wins over the entry id.
            'id' => 'sub-1',
            'summary' => 'Session one summary',
        ])
        ->and($response->toArray()['sessions'])->toBe([$session->id()]);
});
