<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashMediaSeriesImporter;
use App\Services\Subsplash\SubsplashMediaItemResponseBuilder;

beforeEach(function () {
    Collection::findByHandle('media_item') ?? Collection::make('media_item')->save();
    Collection::findByHandle('media_series') ?? Collection::make('media_series')->save();

    $this->apiClient = Mockery::mock(SubsplashApiClient::class);
    $this->seriesImporter = Mockery::mock(SubsplashMediaSeriesImporter::class);

    $this->app->instance(SubsplashApiClient::class, $this->apiClient);
    $this->app->instance(SubsplashMediaSeriesImporter::class, $this->seriesImporter);

    $this->builder = app(SubsplashMediaItemResponseBuilder::class);
});

afterEach(function () {
    Mockery::close();
});

it('skips related media items whose Subsplash lookup returns null', function () {
    $primaryEntry = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->id('primary-id')
        ->slug('primary');
    $primaryEntry->set('subsplash_media_item_id', 'primary-id');
    $primaryEntry->save();

    $seriesEntry = Entry::make()
        ->collection('media_series')
        ->blueprint('subsplash_media_series')
        ->id('series-id')
        ->slug('the-series');
    $seriesEntry->save();

    $okChild = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->id('child-ok')
        ->slug('child-ok');
    $okChild->set('subsplash_media_item_id', 'child-ok');
    $okChild->set('series', 'entry::series-id');
    $okChild->published(true);
    $okChild->date(now()->subDay());
    $okChild->save();

    $missingChild = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->id('child-missing')
        ->slug('child-missing');
    $missingChild->set('subsplash_media_item_id', 'child-missing');
    $missingChild->set('series', 'entry::series-id');
    $missingChild->published(true);
    $missingChild->date(now()->subDays(2));
    $missingChild->save();

    $primarySubsplashResponse = [
        'title' => 'Primary',
        'short_code' => 'abc',
        'date' => '2024-01-15',
        '_embedded' => [
            'media-series' => [
                'id' => 'series-id',
                '_links' => [
                    'media-items' => ['href' => 'https://api.subsplash.test/media-items?series=series-id'],
                ],
            ],
        ],
    ];

    $this->apiClient->shouldReceive('getMediaItem')
        ->with('primary-id')
        ->andReturn($primarySubsplashResponse);

    $this->apiClient->shouldReceive('fetch')->never();

    $this->apiClient->shouldReceive('getMediaItem')
        ->with('child-ok')
        ->andReturn([
            'id' => 'child-ok',
            'title' => 'OK Child',
        ]);

    $this->apiClient->shouldReceive('getMediaItem')
        ->with('child-missing')
        ->andReturn(null);

    $this->seriesImporter->shouldReceive('findInStatamic')
        ->with('series-id')
        ->andReturn($seriesEntry);

    $response = $this->builder->build($primaryEntry);

    expect($response)->toBeArray()
        ->and($response['mediaItems'])->toHaveCount(1)
        ->and($response['mediaItems'][0]['id'])->toBe('child-ok')
        ->and($response['mediaItems'][0]['slug'])->toBe('child-ok');
});

it('falls back to public CMS fields when the Subsplash item is missing', function (?array $subsplashResponse) {
    $entry = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->id('deleted-id')
        ->slug('deleted-item')
        ->date(now()->subDay());
    $entry->set('subsplash_media_item_id', 'deleted-id');
    $entry->set('title', 'Deleted Item');
    $entry->set('description', 'Still in the CMS');
    $entry->save();

    $this->apiClient->shouldReceive('getMediaItem')
        ->with('deleted-id')
        ->andReturn($subsplashResponse);

    $response = json_decode(json_encode($this->builder->build($entry)), true);

    expect(array_keys($response))->toEqualCanonicalizing([
        'id',
        'slug',
        'uri',
        'blueprint',
        'title',
        'date',
        'summary',
        'is_vertical_video',
        'images',
    ])
        ->and($response['slug'])->toBe('deleted-item')
        ->and($response['title'])->toBe('Deleted Item')
        ->and($response['summary'])->toBe('Still in the CMS')
        ->and($response['date'])->not->toBeNull()
        ->and($response['blueprint']['handle'])->toBe('subsplash_media_item');
})->with([
    'a 404 from Subsplash' => [null],
    'a resource_not_found payload' => [['errors' => [['code' => 'resource_not_found']]]],
]);
