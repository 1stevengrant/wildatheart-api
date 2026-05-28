<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use App\Services\MediaOrSeriesResolver;

beforeEach(function () {
    Collection::findByHandle('media_item') ?? Collection::make('media_item')->save();
    Collection::findByHandle('media_series') ?? Collection::make('media_series')->save();

    $this->resolver = app(MediaOrSeriesResolver::class);
});

function makeSubsplashSeries(string $id, string $slug): Statamic\Contracts\Entries\Entry
{
    $series = Entry::make()
        ->collection('media_series')
        ->blueprint('subsplash_media_series')
        ->id($id)
        ->slug($slug);
    $series->published(true);
    $series->date(now()->subDay());
    $series->save();

    return $series;
}

function makeCustomSeries(string $id, string $slug): Statamic\Contracts\Entries\Entry
{
    $series = Entry::make()
        ->collection('media_series')
        ->blueprint('custom_series')
        ->id($id)
        ->slug($slug);
    $series->published(true);
    $series->date(now()->subDay());
    $series->save();

    return $series;
}

function makeMediaItem(string $id, string $slug, string $seriesId, array $overrides = []): Statamic\Contracts\Entries\Entry
{
    $item = Entry::make()
        ->collection('media_item')
        ->blueprint($overrides['blueprint'] ?? 'subsplash_media_item')
        ->id($id)
        ->slug($slug);
    $item->set('subsplash_media_item_id', $id);
    $item->set('series', 'entry::' . $seriesId);
    $item->published($overrides['published'] ?? true);
    $item->date($overrides['date'] ?? now()->subDay());

    if (array_key_exists('make_scheduled_page_public', $overrides)) {
        $item->set('make_scheduled_page_public', $overrides['make_scheduled_page_public']);
    }

    $item->save();

    return $item;
}

it('swaps a single-segment subsplash series with exactly one published, non-private item to that item', function () {
    $series = makeSubsplashSeries('series-1', 'series-1');
    $item = makeMediaItem('item-1', 'item-1', 'series-1');

    $resolved = $this->resolver->resolve('series-1');

    expect($resolved->id())->toBe($item->id())
        ->and($resolved->blueprint()->handle())->toBe('subsplash_media_item');
});

it('returns the series payload when the subsplash series has zero published items', function () {
    $series = makeSubsplashSeries('series-empty', 'series-empty');

    $resolved = $this->resolver->resolve('series-empty');

    expect($resolved->id())->toBe($series->id())
        ->and($resolved->blueprint()->handle())->toBe('subsplash_media_series');
});

it('returns the series payload when the subsplash series has two or more published items', function () {
    $series = makeSubsplashSeries('series-multi', 'series-multi');
    makeMediaItem('item-a', 'item-a', 'series-multi');
    makeMediaItem('item-b', 'item-b', 'series-multi');

    $resolved = $this->resolver->resolve('series-multi');

    expect($resolved->id())->toBe($series->id())
        ->and($resolved->blueprint()->handle())->toBe('subsplash_media_series');
});

it('does not count draft items toward the single-item swap', function () {
    $series = makeSubsplashSeries('series-with-draft', 'series-with-draft');
    makeMediaItem('item-visible', 'item-visible', 'series-with-draft');
    makeMediaItem('item-draft', 'item-draft', 'series-with-draft', ['published' => false]);

    $resolved = $this->resolver->resolve('series-with-draft');

    expect($resolved->blueprint()->handle())->toBe('subsplash_media_item')
        ->and($resolved->id())->toBe('item-visible');
});

it('does not count future-dated private items toward the single-item swap', function () {
    $series = makeSubsplashSeries('series-with-private', 'series-with-private');
    makeMediaItem('item-public', 'item-public', 'series-with-private');
    makeMediaItem('item-private', 'item-private', 'series-with-private', [
        'date' => now()->addDays(7),
        'make_scheduled_page_public' => false,
    ]);

    $resolved = $this->resolver->resolve('series-with-private');

    expect($resolved->blueprint()->handle())->toBe('subsplash_media_item')
        ->and($resolved->id())->toBe('item-public');
});

it('does not swap when the request is a two-segment URI that resolves directly to a media item', function () {
    $series = makeSubsplashSeries('series-direct', 'series-direct');
    $item = makeMediaItem('item-direct', 'item-direct', 'series-direct');

    $resolved = $this->resolver->resolve('series-direct/item-direct');

    expect($resolved->id())->toBe($item->id())
        ->and($resolved->blueprint()->handle())->toBe('subsplash_media_item');
});

it('never swaps custom_series regardless of item count', function () {
    $series = makeCustomSeries('custom-series-1', 'custom-series-1');
    makeMediaItem('custom-item-1', 'custom-item-1', 'custom-series-1', [
        'blueprint' => 'custom_media_item',
    ]);

    $resolved = $this->resolver->resolve('custom-series-1');

    expect($resolved->id())->toBe($series->id())
        ->and($resolved->blueprint()->handle())->toBe('custom_series');
});

it('returns null when no entry matches the slug or URI', function () {
    expect($this->resolver->resolve('does-not-exist'))->toBeNull();
});

it('honours the swap opt-out flag and returns the series even when a single item exists', function () {
    $series = makeSubsplashSeries('series-optout', 'series-optout');
    makeMediaItem('item-optout', 'item-optout', 'series-optout');

    $resolved = $this->resolver->resolve('series-optout', swapSingleItemSeries: false);

    expect($resolved->id())->toBe($series->id())
        ->and($resolved->blueprint()->handle())->toBe('subsplash_media_series');
});
