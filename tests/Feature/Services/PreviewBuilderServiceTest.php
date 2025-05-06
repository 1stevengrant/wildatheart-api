<?php

use Statamic\Facades\Entry;
use App\Services\BunnyCDNService;
use App\Services\SubsplashService;
use App\DataTypeObjects\ContentPreview;
use App\Services\PreviewBuilderService;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Create mock services
    $this->subsplashService = Mockery::mock(SubsplashService::class);
    $this->bunnyCDNService = Mockery::mock(BunnyCDNService::class);

    // Create PreviewBuilderService with mocked dependencies
    $this->service = new PreviewBuilderService(
        $this->bunnyCDNService,
        $this->subsplashService
    );
});

it('Returns content preview using data from Subsplash', function () {
    // Mock the subsplash response
    $mockSubsplashResponse = [
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

    $this->subsplashService
        ->shouldReceive('getMediaItem')
        ->once()
        ->with('test-id')
        ->andReturn($mockSubsplashResponse);

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
    // Mock the subsplash response
    $mockSubsplashResponse = [
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

    $this->subsplashService
        ->shouldReceive('getMediaSeries')
        ->once()
        ->with('test-series-id')
        ->andReturn($mockSubsplashResponse);

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
    // Mock the subsplash response
    $mockSubsplashResponse = [
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

    $this->subsplashService
        ->shouldReceive('getMediaSeries')
        ->once()
        ->with('test-podcast-id')
        ->andReturn($mockSubsplashResponse);

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
