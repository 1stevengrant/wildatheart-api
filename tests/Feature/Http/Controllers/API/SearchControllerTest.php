<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;

/**
 * @covers \App\Http\Controllers\API\SearchController
 */
beforeEach(function () {
    if (! Collection::handleExists('blogs')) {
        Collection::make('blogs')->dated(true)->save();
    }

    // Serialize like the production cache stores do, so cached objects come back as
    // __PHP_Incomplete_Class while cache.serializable_classes is false.
    config(['cache.stores.array.serialize' => true]);
    cache()->forgetDriver('array');
});

it('returns the same entry list when the response is served from cache', function () {
    Entry::make()
        ->collection('blogs')
        ->blueprint('blog')
        ->slug('cached-search-entry')
        ->published(true)
        ->date(now()->addYear())
        ->data(['title' => 'Cached Search Entry', 'content' => 'Body text'])
        ->save();

    $first = $this->getJson(route('content.v1.search.index'));
    $cached = $this->getJson(route('content.v1.search.index'));

    $first->assertOk()->assertJsonPath('data.0.title', 'Cached Search Entry');
    $cached->assertOk()->assertJsonPath('data.0.title', 'Cached Search Entry');
    expect($cached->json('data'))->toBeList();
    expect($cached->json())->toEqual($first->json());
});
