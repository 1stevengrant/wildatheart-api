<?php

use Statamic\Facades\Term;
use Statamic\Facades\Entry;
use App\Services\BunnyCDNService;
use App\DataTypeObjects\SlideBlock;
use App\Services\BlockBuilderService;
use App\Services\PreviewBuilderService;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashMediaSeriesImporter;

beforeEach(function () {
    $this->apiClient = Mockery::mock(SubsplashApiClient::class);
    $this->bunnyCDNService = Mockery::mock(BunnyCDNService::class);
    $this->seriesImporter = Mockery::mock(SubsplashMediaSeriesImporter::class);

    // Create PreviewBuilderService with mocked dependencies
    $previewBuilderService = new PreviewBuilderService(
        $this->bunnyCDNService,
        $this->apiClient
    );
    $this->service = new BlockBuilderService($previewBuilderService, $this->apiClient, $this->seriesImporter, $this->bunnyCDNService);
});

it('it builds a content_in_taxonomy slide', function () {

    $mediaItem = Entry::make()
        ->collection('media_item')
        ->slug('podcast-1');

    $mediaItem->set('title', 'test item');
    $mediaItem->save();

    $entry = Entry::make()->collection('pages')->slug('test');

    $testBlocks = [[
        'section_title' => 'Video Series',
        'link_to_more' => null,
        'media_series' => null,
        'media_items' => null,
        'focused_item' => null,
        'slides' => [
            [
                'limit' => 10,
                'enabled' => true,
                'type' => 'content_in_taxonomy',
                'collections_to_load_from' => [
                    'media_item',
                ],
            ],
        ],
        'type' => 'media_slider',
    ]];
    $entry->set('blocks', $testBlocks);

    $result = $this->service->build($entry->blocks);

    expect($result[0]['slides'])->toHaveCount(1)
        ->and($result[0]['slides'][0])->toBeInstanceOf(SlideBlock::class)
        ->and($result[0]['slides'][0]->title)->toBe('test item');
});

it('it only returns slides that have the appropriate taxonomy', function () {

    $mediaItem = Entry::make()
        ->collection('media_item')
        ->slug('podcast-1');

    $mediaItem->set('title', 'some other media');
    $mediaItem->save();

    $liveTalk = Entry::make()
        ->collection('media_item')
        ->slug('live-talk-1');

    $liveTalk->set('title', 'live talk');
    $liveTalk->set('content_type', ['live-talks']);
    $liveTalk->save();

    $entry = Entry::make()->collection('pages')->slug('test');

    Term::make()
        ->taxonomy('content_type')
        ->slug('live-talks')
        ->data(['title' => 'Live Talks'])
        ->save();

    $testBlocks = [[
        'section_title' => 'Video Series',
        'link_to_more' => null,
        'media_series' => null,
        'media_items' => null,
        'focused_item' => null,
        'slides' => [
            [
                'limit' => 10,
                'enabled' => true,
                'type' => 'content_in_taxonomy',
                'tags' => [
                    'content_type::live-talks',
                ],
                'collections_to_load_from' => [
                    'media_item',
                ],
            ],
        ],
        'type' => 'media_slider',
    ]];
    $entry->set('blocks', $testBlocks);

    $result = $this->service->build($entry->blocks);

    expect($result[0]['slides'])->toHaveCount(1)
        ->and($result[0]['slides'][0])->toBeInstanceOf(SlideBlock::class)
        ->and($result[0]['slides'][0]->title)->toBe('live talk');
});
