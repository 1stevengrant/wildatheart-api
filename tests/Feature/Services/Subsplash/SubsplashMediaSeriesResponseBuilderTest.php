<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashMediaSeriesResponseBuilder;

beforeEach(function () {
    Collection::findByHandle('media_item') ?? Collection::make('media_item')->save();
    Collection::findByHandle('media_series') ?? Collection::make('media_series')->save();

    $this->apiClient = Mockery::mock(SubsplashApiClient::class);
    $this->app->instance(SubsplashApiClient::class, $this->apiClient);

    $this->builder = app(SubsplashMediaSeriesResponseBuilder::class);
});

afterEach(function () {
    Mockery::close();
});

function makeSeries(string $id, string $slug, string $subsplashId): Statamic\Contracts\Entries\Entry
{
    $series = Entry::make()
        ->collection('media_series')
        ->blueprint('subsplash_media_series')
        ->id($id)
        ->slug($slug);
    $series->set('title', 'The Series');
    $series->set('subsplash_media_series_id', $subsplashId);
    $series->published(true);
    $series->date(now()->subDay());
    $series->save();

    return $series;
}

function makeSeriesMediaItem(string $id, string $slug, array $overrides = []): Statamic\Contracts\Entries\Entry
{
    $item = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->id($id)
        ->slug($slug);
    $item->set('subsplash_media_item_id', $id);
    $item->published($overrides['published'] ?? true);
    $item->date($overrides['date'] ?? now()->subDay());

    if (array_key_exists('make_scheduled_page_public', $overrides)) {
        $item->set('make_scheduled_page_public', $overrides['make_scheduled_page_public']);
    }

    $item->save();

    return $item;
}

it('excludes unpublished media items from the series mediaItems payload', function () {
    $series = makeSeries('series-id', 'the-series', 'ss-series-123');

    makeSeriesMediaItem('item-published', 'item-published');
    makeSeriesMediaItem('item-draft', 'item-draft', ['published' => false]);

    $this->apiClient->shouldReceive('getMediaSeries')
        ->with('ss-series-123')
        ->andReturn([
            'subtitle' => 'A subtitle',
            '_embedded' => ['media-series' => []],
            '_links' => [
                'media-items' => ['href' => 'https://api.subsplash.test/media-items?series=ss-series-123'],
            ],
        ]);

    $this->apiClient->shouldReceive('fetch')
        ->andReturn([
            '_embedded' => [
                'media-items' => [
                    ['id' => 'item-published'],
                    ['id' => 'item-draft'],
                ],
            ],
            '_links' => [],
        ]);

    $response = $this->builder->build($series);

    $ids = array_column($response['mediaItems'], 'id');

    expect($response)->toBeArray()
        ->and($response['mediaItems'])->toHaveCount(1)
        ->and($ids)->toContain('item-published')
        ->and($ids)->not->toContain('item-draft');
});

it('excludes future-dated, non-public media items from the series mediaItems payload', function () {
    $series = makeSeries('series-id-2', 'the-series-2', 'ss-series-456');

    makeSeriesMediaItem('item-live', 'item-live');
    makeSeriesMediaItem('item-private', 'item-private', [
        'date' => now()->addDays(7),
        'make_scheduled_page_public' => false,
    ]);

    $this->apiClient->shouldReceive('getMediaSeries')
        ->with('ss-series-456')
        ->andReturn([
            '_links' => [
                'media-items' => ['href' => 'https://api.subsplash.test/media-items?series=ss-series-456'],
            ],
        ]);

    $this->apiClient->shouldReceive('fetch')
        ->andReturn([
            '_embedded' => [
                'media-items' => [
                    ['id' => 'item-live'],
                    ['id' => 'item-private'],
                ],
            ],
            '_links' => [],
        ]);

    $response = $this->builder->build($series);

    $ids = array_column($response['mediaItems'], 'id');

    expect($response['mediaItems'])->toHaveCount(1)
        ->and($ids)->toContain('item-live')
        ->and($ids)->not->toContain('item-private');
});

it('resolves the contained media item entries in a single query regardless of item count', function () {
    $series = makeSeries('series-id-3', 'the-series-3', 'ss-series-789');

    makeSeriesMediaItem('item-one', 'item-one');
    makeSeriesMediaItem('item-two', 'item-two');
    makeSeriesMediaItem('item-three', 'item-three');

    $this->apiClient->shouldReceive('getMediaSeries')
        ->with('ss-series-789')
        ->andReturn([
            '_links' => [
                'media-items' => ['href' => 'https://api.subsplash.test/media-items?series=ss-series-789'],
            ],
        ]);

    $this->apiClient->shouldReceive('fetch')
        ->andReturn([
            '_embedded' => [
                'media-items' => [
                    ['id' => 'item-one'],
                    ['id' => 'item-two'],
                    ['id' => 'item-three'],
                ],
            ],
            '_links' => [],
        ]);

    $lookupQueryCount = 0;
    DB::listen(function ($query) use (&$lookupQueryCount) {
        if (str_contains($query->sql, 'subsplash_media_item_id')) {
            $lookupQueryCount++;
        }
    });

    $response = $this->builder->build($series);

    expect($lookupQueryCount)->toBe(1)
        ->and(array_column($response['mediaItems'], 'id'))->toBe(['item-one', 'item-two', 'item-three']);
});

it('returns a consistent array shape with an empty mediaItems list when the Subsplash series is not found', function () {
    $series = makeSeries('series-missing', 'missing-series', 'ss-missing');

    $this->apiClient->shouldReceive('getMediaSeries')
        ->with('ss-missing')
        ->andReturn([
            'errors' => [
                ['code' => 'resource_not_found'],
            ],
        ]);

    $response = $this->builder->build($series);

    expect($response)->toBeArray()
        ->and($response['id'])->toBe('series-missing')
        ->and($response['slug'])->toBe('missing-series')
        ->and($response)->toHaveKey('mediaItems')
        ->and($response['mediaItems'])->toBe([]);
});
