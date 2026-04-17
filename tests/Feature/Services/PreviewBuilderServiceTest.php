<?php

use Statamic\Facades\Entry;
use App\Services\BunnyCDNService;
use App\DataTypeObjects\ContentPreview;
use App\Services\PreviewBuilderService;
use App\Services\Subsplash\SubsplashApiClient;

beforeEach(function () {
    $this->apiClient = Mockery::mock(SubsplashApiClient::class);
    $this->bunnyCDNService = Mockery::mock(BunnyCDNService::class);

    $this->service = new PreviewBuilderService(
        $this->bunnyCDNService,
        $this->apiClient
    );

    $this->mockSubsplashResponse = [
        '_embedded' => [
            'images' => [
                [
                    'type' => 'wide',
                    '_embedded' => [
                        'source' => [
                            '_links' => [
                                'related' => [
                                    'href' => 'https://example.com/test-image.jpg',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'summary' => 'Test description',
    ];
});

it('Returns content preview using data from Subsplash', function () {
    $this->apiClient
        ->shouldReceive('getMediaItem')
        ->once()
        ->with('test-id')
        ->andReturn($this->mockSubsplashResponse);

    $entry = Entry::make()
        ->blueprint('subsplash_media_item')
        ->collection('media_item')
        ->id('test-id')
        ->slug('test');

    $entry->set('title', 'test title');
    $entry->set('subsplash_media_item_id', 'test-id');
    $entry->save();

    $preview = $this->service->build($entry);

    expect($preview)
        ->toBeInstanceOf(ContentPreview::class)
        ->and($preview->title)->toBe('test title')
        ->and($preview->featured_image_src)->toBe('https://example.com/test-image.jpg')
        ->and($preview->description)->toBe('Test description');
});

it('Returns content preview for subsplash series', function () {
    $this->apiClient
        ->shouldReceive('getMediaSeries')
        ->once()
        ->with('test-series-id')
        ->andReturn($this->mockSubsplashResponse);

    $entry = Entry::make()
        ->blueprint('subsplash_media_series')
        ->collection('media_series')
        ->id('test-series-id')
        ->slug('test');

    $entry->set('title', 'test title');
    $entry->set('subsplash_series_id', 'test-series-id');
    $entry->save();

    $preview = $this->service->build($entry);

    expect($preview)
        ->toBeInstanceOf(ContentPreview::class)
        ->and($preview->title)->toBe('test title')
        ->and($preview->featured_image_src)->toBe('https://example.com/test-image.jpg')
        ->and($preview->description)->toBe('Test description');
});

it('Returns content preview for Podcast series', function () {
    $this->apiClient
        ->shouldReceive('getMediaSeries')
        ->once()
        ->with('test-podcast-id')
        ->andReturn($this->mockSubsplashResponse);

    $entry = Entry::make()
        ->blueprint('podcast')
        ->collection('podcasts')
        ->id('test-podcast-id')
        ->slug('test-podcast');

    $entry->set('title', 'test title');
    $entry->set('description', 'description from statamic');
    $entry->set('subsplash_series_id', 'test-podcast-id');
    $entry->save();

    $preview = $this->service->build($entry);

    expect($preview)
        ->toBeInstanceOf(ContentPreview::class)
        ->and($preview->title)->toBe('test title')
        ->and($preview->featured_image_src)->toBe('https://example.com/test-image.jpg')
        ->and($preview->description)->toBe("description from statamic\n");
});
