<?php

namespace Tests\Feature\Services;

use Mockery;
use Carbon\Carbon;
use Statamic\Facades\Term;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\AssetContainer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashTopicService;
use App\Services\Subsplash\SubsplashAssetImporter;
use App\Services\Subsplash\SubsplashMediaItemImporter;
use App\Services\Subsplash\SubsplashMediaSeriesImporter;

beforeEach(function () {
    Bus::fake();
    Cache::flush();

    $this->apiClient = new SubsplashApiClient;
    $this->assetImporter = new SubsplashAssetImporter;
    $this->topicService = new SubsplashTopicService;
    $this->seriesImporter = new SubsplashMediaSeriesImporter($this->apiClient);
    $this->importer = new SubsplashMediaItemImporter(
        $this->apiClient,
        $this->assetImporter,
        $this->topicService,
        $this->seriesImporter,
    );

    // Setup S3 asset container for testing
    Storage::fake('s3');
    $container = AssetContainer::make('s3')
        ->disk('s3')
        ->save();
});

afterEach(function () {
    Mockery::close();
});

describe('saveMediaItemAsEntry', function () {

    it('creates a new media item entry from subsplash data', function () {
        $mediaItem = createMediaItemData();

        $entry = $this->importer->import($mediaItem);

        expect($entry)->not->toBeNull()
            ->and($entry->collection()->handle())->toBe('media_item')
            ->and($entry->blueprint()->handle())->toBe('subsplash_media_item')
            ->and($entry->id())->toBe('test-media-item-123')
            ->and($entry->get('title'))->toBe('Test Media Item')
            ->and($entry->get('subtitle'))->toBe('A subtitle')
            ->and($entry->get('speaker'))->toBe('John Doe')
            ->and($entry->get('description'))->toBe('A test summary')
            ->and($entry->get('subsplash_media_item_id'))->toBe('test-media-item-123')
            ->and($entry->published())->toBeTrue();
    });

    it('updates an existing media item entry', function () {
        $mediaItem = createMediaItemData();

        // Create initial entry
        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('test-media-item-123')
            ->slug('test-media-item')
            ->published(true)
            ->data([
                'title' => 'Old Title',
                'subsplash_media_item_id' => 'test-media-item-123',
                'last_imported' => Carbon::parse('2024-01-01')->toIso8601String(),
            ])
            ->save();

        $entry = $this->importer->import($mediaItem);

        expect($entry->id())->toBe('test-media-item-123')
            ->and($entry->get('title'))->toBe('Test Media Item')
            ->and($entry->get('title_subsplash'))->toBe('Test Media Item');
    });

    it('skips import when data has not changed since last import', function () {
        $callbackCalled = false;
        $callbackAction = null;

        $mediaItem = createMediaItemData();
        $mediaItem['updated_at'] = '2024-01-01T10:00:00Z';

        // Create entry with recent last_imported date
        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('test-media-item-123')
            ->slug('test-media-item')
            ->published(true)
            ->data([
                'title' => 'Test Media Item',
                'subsplash_media_item_id' => 'test-media-item-123',
                'last_imported' => Carbon::parse('2024-01-02')->toIso8601String(), // After updated_at
                'last_updated' => Carbon::parse('2024-01-01T10:00:00Z')->toIso8601String(),
            ])
            ->save();

        $callback = function ($action) use (&$callbackCalled, &$callbackAction) {
            $callbackCalled = true;
            $callbackAction = $action;
        };

        $entry = $this->importer->import($mediaItem, false, false, $callback);

        expect($callbackCalled)->toBeTrue()
            ->and($callbackAction)->toBe('skipped')
            ->and($entry->id())->toBe('test-media-item-123');
    });

    it('forces import when force flag is true even when data has not changed', function () {
        $mediaItem = createMediaItemData();
        $mediaItem['updated_at'] = '2024-01-01T10:00:00Z';

        $oldLastImported = Carbon::parse('2024-01-02')->toIso8601String();
        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('test-media-item-123')
            ->slug('test-media-item')
            ->published(true)
            ->data([
                'title' => 'Test Media Item',
                'subsplash_media_item_id' => 'test-media-item-123',
                'last_imported' => $oldLastImported, // After updated_at
                'last_updated' => Carbon::parse('2024-01-01T10:00:00Z')->toIso8601String(),
            ])
            ->save();

        $callbackCalled = false;
        $callback = function ($action) use (&$callbackCalled) {
            $callbackCalled = true;
        };

        $entry = $this->importer->import($mediaItem, false, true, $callback);

        // Callback should NOT be called since we're forcing the import
        expect($callbackCalled)->toBeFalse()
            ->and($entry->id())->toBe('test-media-item-123')
            ->and($entry->data()->get('last_imported'))->not->toBe($oldLastImported); // Should have a new last_imported timestamp
    });

    it('returns custom media item without updating if it exists', function () {
        $mediaItem = createMediaItemData();

        // Create a custom media item
        $customEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('custom_media_item')
            ->id('custom-123')
            ->slug('custom-item')
            ->published(true)
            ->data([
                'title' => 'Custom Item',
                'subsplash_media_item_id' => 'test-media-item-123',
            ])
            ->save();

        $entry = $this->importer->import($mediaItem);

        expect($entry->id())->toBe('custom-123')
            ->and($entry->blueprint()->handle())->toBe('custom_media_item')
            ->and($entry->get('title'))->toBe('Custom Item'); // Not updated
    });

    it('processes and saves subsplash fields correctly', function () {
        $mediaItem = createMediaItemData();

        $entry = $this->importer->import($mediaItem);

        expect($entry->get('title_subsplash'))->toBe('Test Media Item')
            ->and($entry->get('subtitle_subsplash'))->toBe('A subtitle')
            ->and($entry->get('speaker_subsplash'))->toBe('John Doe')
            ->and($entry->get('description_subsplash'))->toBe('A test summary')
            ->and($entry->get('date_subsplash'))->toBe('2024-01-15')
            ->and($entry->get('duration_subsplash'))->toBe('00:45:30')
            ->and($entry->get('video_iframe_url_subsplash'))->toContain('subsplash.com')
            ->and($entry->get('audio_direct_url_subsplash'))->toContain('audio')
            ->and($entry->get('video_direct_url_subsplash'))->toContain('video');
    });

    it('processes images from media item', function () {
        $mediaItem = createMediaItemData();

        // Mock image download
        Http::fake([
            'https://images.subsplash.com/*' => Http::response('fake-image-content', 200),
        ]);

        $entry = $this->importer->import($mediaItem);

        $images = $entry->get('images_subsplash');

        expect($images)->toBeArray()
            ->and($images)->toHaveCount(2)
            ->and($images[0]['image_type_subsplash'])->toBe('wide')
            ->and($images[1]['image_type_subsplash'])->toBe('square');
    });

    it('processes topics from tags', function () {
        $mediaItem = createMediaItemData();
        $mediaItem['tags'] = ['topic:Faith', 'topic:Prayer', 'other-tag'];

        $entry = $this->importer->import($mediaItem);

        $topicIds = $entry->get('topics_subsplash');

        expect($topicIds)->toBeArray()
            ->and($topicIds)->toHaveCount(2);

        // Verify topics were created
        $faithTopic = Term::query()
            ->where('taxonomy', 'topics')
            ->where('slug', 'faith')
            ->first();

        $prayerTopic = Term::query()
            ->where('taxonomy', 'topics')
            ->where('slug', 'prayer')
            ->first();

        expect($faithTopic)->not->toBeNull()
            ->and($faithTopic->get('title'))->toBe('Faith')
            ->and($prayerTopic)->not->toBeNull()
            ->and($prayerTopic->get('title'))->toBe('Prayer');
    });

    it('associates media series when present', function () {
        // Create a media series first
        $seriesEntry = Entry::make()
            ->collection('media_series')
            ->blueprint('subsplash_media_series')
            ->id('series-123')
            ->slug('test-series')
            ->published(true)
            ->data([
                'title' => 'Test Series',
                'subsplash_media_series_id' => 'series-123',
            ])
            ->save();

        $mediaItem = createMediaItemData();
        $mediaItem['_embedded']['media-series'] = [
            'id' => 'series-123',
        ];

        $entry = $this->importer->import($mediaItem);

        expect($entry->get('series'))->toBe('entry::series-123');
    });

    it('generates unique slug for new entries', function () {
        $mediaItem = createMediaItemData();

        $entry = $this->importer->import($mediaItem);

        expect($entry->slug())->toBe('test-media-item');
    });

    it('updates slug when updateSlug parameter is true', function () {
        $mediaItem = createMediaItemData();
        $mediaItem['title'] = 'Updated Title';

        // Create existing entry
        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('test-media-item-123')
            ->slug('old-slug')
            ->published(true)
            ->data([
                'title' => 'Old Title',
                'subsplash_media_item_id' => 'test-media-item-123',
            ])
            ->save();

        $entry = $this->importer->import($mediaItem, true);

        expect($entry->slug())->toBe('updated-title');
    });

    it('respects override_subsplash_content flag', function () {
        $mediaItem = createMediaItemData();
        $mediaItem['title'] = 'Subsplash Title';
        $mediaItem['speaker'] = 'Subsplash Speaker';

        // Create entry with override flag
        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('test-media-item-123')
            ->slug('test-item')
            ->published(true)
            ->data([
                'title' => 'Custom Title',
                'speaker' => 'Custom Speaker',
                'subsplash_media_item_id' => 'test-media-item-123',
                'override_subsplash_content' => true,
            ])
            ->save();

        $entry = $this->importer->import($mediaItem);

        // Main fields should NOT be updated
        expect($entry->get('title'))->toBe('Custom Title')
            ->and($entry->get('speaker'))->toBe('Custom Speaker')
          // But subsplash fields should still be updated
            ->and($entry->get('title_subsplash'))->toBe('Subsplash Title')
            ->and($entry->get('speaker_subsplash'))->toBe('Subsplash Speaker');
    });

    it('calls callback with correct parameters', function () {
        $callbackCalls = [];

        $mediaItem = createMediaItemData();
        $mediaItem['updated_at'] = '2024-01-01T10:00:00Z';

        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('test-media-item-123')
            ->slug('test-media-item')
            ->published(true)
            ->data([
                'title' => 'Test Media Item',
                'subsplash_media_item_id' => 'test-media-item-123',
                'last_imported' => Carbon::parse('2024-01-02')->toIso8601String(),
                'last_updated' => Carbon::parse('2024-01-01T10:00:00Z')->toIso8601String(),
            ])
            ->save();

        $callback = function (...$args) use (&$callbackCalls) {
            $callbackCalls[] = $args;
        };

        $entry = $this->importer->import($mediaItem, false, false, $callback);

        expect($callbackCalls)->toHaveCount(1)
            ->and($callbackCalls[0][0])->toBe('skipped')
            ->and($callbackCalls[0][1])->toBe('Test Media Item');
    });

    it('handles podcast episodes correctly', function () {
        // Create a podcast series first
        $podcastSeries = Entry::make()
            ->collection('podcasts')
            ->blueprint('podcast')
            ->id('podcast-series-123')
            ->slug('test-podcast')
            ->published(true)
            ->data([
                'title' => 'Test Podcast',
                'subsplash_series_id' => 'series-456',
            ])
            ->save();

        // Create a media series
        $mediaSeries = Entry::make()
            ->collection('media_series')
            ->blueprint('subsplash_media_series')
            ->id('series-456')
            ->slug('test-series')
            ->published(true)
            ->data([
                'title' => 'Test Series',
                'subsplash_media_series_id' => 'series-456',
            ])
            ->save();

        $mediaItem = createMediaItemData();
        $mediaItem['_embedded']['media-series'] = [
            'id' => 'series-456',
        ];

        $entry = $this->importer->import($mediaItem);

        expect($entry->get('media_type'))->toBe(['podcast-episode'])
            ->and($entry->get('series'))->toBe('entry::series-456');
    });

    it('stores last_imported and last_updated timestamps', function () {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $mediaItem = createMediaItemData();
        $mediaItem['updated_at'] = '2024-01-15T10:30:00Z';

        $entry = $this->importer->import($mediaItem);

        $lastImported = $entry->get('last_imported');
        $lastUpdated = $entry->get('last_updated');

        expect($lastImported)->toBeInstanceOf(Carbon::class)
            ->and($lastImported->toIso8601String())->toBe($now->toIso8601String())
            ->and($lastUpdated)->toBeInstanceOf(Carbon::class)
            ->and($lastUpdated->toIso8601String())->toBe(Carbon::parse('2024-01-15T10:30:00Z')->toIso8601String());

        Carbon::setTestNow();
    });

    it('handles missing optional fields gracefully', function () {
        $mediaItem = [
            'id' => 'test-media-item-123',
            'title' => 'Minimal Item',
            'date' => '2024-01-15',
            'updated_at' => '2024-01-15T10:00:00Z',
            'short_code' => 'abc123',
            'tags' => [],
            '_embedded' => [],
        ];

        $entry = $this->importer->import($mediaItem);

        expect($entry)->not->toBeNull()
            ->and($entry->get('title'))->toBe('Minimal Item')
            ->and($entry->get('subtitle'))->toBeNull()
            ->and($entry->get('speaker'))->toBeNull()
            ->and($entry->get('description'))->toBeNull();
    });

    it('finds existing entry by subsplash_media_item_id in data', function () {
        // Create entry where ID doesn't match but subsplash_media_item_id does
        $existingEntry = Entry::make()
            ->collection('media_item')
            ->blueprint('subsplash_media_item')
            ->id('different-id-456')
            ->slug('test-item')
            ->published(true)
            ->data([
                'title' => 'Existing Title',
                'subsplash_media_item_id' => 'test-media-item-123',
            ])
            ->save();

        $mediaItem = createMediaItemData();

        $entry = $this->importer->import($mediaItem);

        // Should update the existing entry, not create a new one
        expect($entry->id())->toBe('different-id-456')
            ->and($entry->get('title'))->toBe('Test Media Item'); // Updated
    });

    it('handles video duration in milliseconds', function () {
        $mediaItem = createMediaItemData();
        // Default already has 2730000 milliseconds (45:30)

        $entry = $this->importer->import($mediaItem);

        expect($entry->get('duration_subsplash'))->toBe('00:45:30');
    });

    it('falls back to audio duration when video duration is missing', function () {
        $mediaItem = createMediaItemData();

        // Remove video duration
        unset($mediaItem['_embedded']['video']);

        // Set audio duration (in milliseconds: 1800000 ms = 30 minutes)
        $mediaItem['_embedded']['audio']['_embedded']['audio-outputs'][0]['duration'] = 1800000;

        $entry = $this->importer->import($mediaItem);

        expect($entry->get('duration_subsplash'))->toBe('00:30:00');
    });
});

// Helper function to create consistent test data
function createMediaItemData(): array
{
    return [
        'id' => 'test-media-item-123',
        'title' => 'Test Media Item',
        'subtitle' => 'A subtitle',
        'summary' => 'A test summary',
        'description' => 'A test description',
        'speaker' => 'John Doe',
        'date' => '2024-01-15',
        'updated_at' => '2024-01-15T10:00:00Z',
        'short_code' => 'abc123',
        'tags' => [],
        '_embedded' => [
            'video' => [
                'duration' => 2730000, // 45:30 in milliseconds
                'title' => 'video',
                '_embedded' => [
                    'video-outputs' => [
                        [
                            '_links' => [
                                'related' => [
                                    'href' => 'https://videos.subsplash.com/{filename}.mp4',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'audio' => [
                'title' => 'audio',
                '_embedded' => [
                    'audio-outputs' => [
                        [
                            'duration' => 2730,
                            '_links' => [
                                'download' => [
                                    'href' => 'https://audio.subsplash.com/{filename}.mp3',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'images' => [
                [
                    'id' => 'image-wide-123',
                    'type' => 'wide',
                    '_links' => [
                        'related' => [
                            'href' => 'https://images.subsplash.com/wide.jpg',
                        ],
                        'dynamic' => [
                            'href' => 'https://images.subsplash.com/{name}.{ext}?w={width}&h={height}',
                        ],
                    ],
                ],
                [
                    'id' => 'image-square-456',
                    'type' => 'square',
                    '_links' => [
                        'related' => [
                            'href' => 'https://images.subsplash.com/square.jpg',
                        ],
                        'dynamic' => [
                            'href' => 'https://images.subsplash.com/{name}.{ext}?w={width}&h={height}',
                        ],
                    ],
                ],
            ],
        ],
    ];
}
