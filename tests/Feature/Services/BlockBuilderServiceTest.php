<?php

use Statamic\Facades\Term;
use Statamic\Facades\Entry;
use Illuminate\Support\Sleep;
use App\Services\BunnyCDNService;
use App\DataTypeObjects\SlideBlock;
use Illuminate\Support\Facades\Http;
use App\Services\BlockBuilderService;
use Illuminate\Support\Facades\Cache;
use App\Services\PreviewBuilderService;
use Illuminate\Support\Facades\Exceptions;
use App\Services\Subsplash\SubsplashApiClient;
use Illuminate\Http\Client\ConnectionException;
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

it('retains local series slides in date order when Subsplash ordering is unavailable', function (string $failure) {
    $this->freezeTime();
    config()->set([
        'services.subsplash.base_url' => 'https://api.subsplash.test',
        'services.subsplash.client_id' => 'series-test-client',
        'cache.default' => 'array',
    ]);
    Cache::put('series-test-client', 'test-token', 3600);
    Sleep::fake();
    Exceptions::fake();
    $seriesId = '88cc543b-484f-4cb2-9130-6b4abe84a03e';
    $series = Entry::make()->collection('media_series')->blueprint('custom_series')
        ->id($seriesId)->slug('resilient-series')->published(true)
        ->data(['title' => 'Resilient series', 'subsplash_media_series_id' => $seriesId]);
    $series->date(now()->subDays(10));
    $series->save();

    foreach (['Newer' => 1, 'Older' => 2] as $title => $daysAgo) {
        $entry = Entry::make()->collection('media_item')->blueprint('custom_media_item')
            ->slug(mb_strtolower($title))->published(true)->data([
                'title' => $title,
                'series' => "entry::{$seriesId}",
                'media_type' => 'Session',
                'images' => ['wide' => null],
            ]);
        $entry->date(now()->subDays($daysAgo));
        $entry->save();
    }

    Http::fake([
        "https://api.subsplash.test/media/v1/media-series/{$seriesId}" => $failure === '503'
            ? Http::response([], 503)
            : Http::failedConnection(),
    ]);
    $this->app->instance(SubsplashApiClient::class, new SubsplashApiClient);
    $service = app(BlockBuilderService::class);
    $slide = (object) ['media_items_from_series' => (object) ['id' => $seriesId]];

    $result = $service->buildMediaItemsFromSeriesSlide($slide);

    expect(collect($result->slides)->pluck('title')->all())->toBe(['Older', 'Newer']);
    Http::assertSentCount(3);
    Exceptions::assertReported($failure === '503' ? Exception::class : ConnectionException::class);
    Sleep::assertSequence([
        Sleep::for(100)->milliseconds(),
        Sleep::for(500)->milliseconds(),
    ]);
})->with(['503', 'connection']);
