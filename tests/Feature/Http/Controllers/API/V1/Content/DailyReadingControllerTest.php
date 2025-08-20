<?php

use function Pest\Laravel\getJson;

use App\Services\TransistorService;

/**
 * @covers \App\Http\Controllers\API\V1\Content\DailyReadingController
 */
beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    // Mock the TransistorService
    $this->transistorServiceMock = Mockery::mock(TransistorService::class);
    $this->app->instance(TransistorService::class, $this->transistorServiceMock);
});

$mockData = [
    [
        'id' => '1299207',
        'type' => 'episode',
        'attributes' => [
            'title' => 'What Jesus Weeps Over',
            'number' => 412,
            'season' => 1,
            'status' => 'published',
            'published_at' => '2024-10-07T07:00:00.000Z',
            'duration' => 93,
            'explicit' => false,
            'keywords' => '',
            'alternate_url' => '',
            'media_url' => 'https://media.transistor.fm/0314dae8/d9bb315b.mp3',
            'image_url' => null,
            'video_url' => '',
            'author' => '',
            'summary' => '',
            'description' => '<div>Test Description</div>',
            'slug' => 'what-jesus-weeps-over',
            'created_at' => '2023-04-19T19:59:02.406Z',
            'updated_at' => '2024-10-07T07:00:04.326Z',
            'formatted_published_at' => 'October 7, 2024',
            'duration_in_mmss' => '01:33',
            'share_url' => 'https://share.transistor.fm/s/0314dae8',
            'transcript_url' => null,
            'transcripts' => [],
            'formatted_summary' => 'Test Description',
            'formatted_description' => '<div>Test Description</div>',
            'embed_html' => '<iframe width="100%" height="180" frameborder="no" scrolling="no" seamless src="https://share.transistor.fm/e/0314dae8"></iframe>',
            'embed_html_dark' => '<iframe width="100%" height="180" frameborder="no" scrolling="no" seamless src="https://share.transistor.fm/e/0314dae8/dark"></iframe>',
            'audio_processing' => false,
            'type' => 'full',
            'email_notifications' => null,
        ],
        'relationships' => [
            'show' => [
                'data' => [
                    'id' => '40884',
                    'type' => 'show',
                ],
            ],
        ],
    ],
    [
        'id' => '1299116',
        'type' => 'episode',
        'attributes' => [
            'title' => 'His Child; His Reflection',
            'number' => 321,
            'season' => 1,
            'status' => 'published',
            'published_at' => '2024-10-06T07:00:00.000Z',
            'duration' => 94,
            'explicit' => false,
            'keywords' => '',
            'alternate_url' => '',
            'media_url' => 'https://media.transistor.fm/43e2f61e/f223dc2e.mp3',
            'image_url' => null,
            'video_url' => '',
            'author' => '',
            'summary' => '',
            'description' => '<div>Test Description</div>',
            'slug' => 'his-child-his-reflection',
            'created_at' => '2023-04-19T19:59:00.912Z',
            'updated_at' => '2024-10-06T07:00:12.439Z',
            'formatted_published_at' => 'October 6, 2024',
            'duration_in_mmss' => '01:34',
            'share_url' => 'https://share.transistor.fm/s/43e2f61e',
            'transcript_url' => null,
            'transcripts' => [],
            'formatted_summary' => 'Test Description',
            'formatted_description' => '<div>Test Description</div>',
            'embed_html' => '<iframe width="100%" height="180" frameborder="no" scrolling="no" seamless src="https://share.transistor.fm/e/43e2f61e"></iframe>',
            'embed_html_dark' => '<iframe width="100%" height="180" frameborder="no" scrolling="no" seamless src="https://share.transistor.fm/e/43e2f61e/dark"></iframe>',
            'audio_processing' => false,
            'type' => 'full',
            'email_notifications' => null,
        ],
        'relationships' => [
            'show' => [
                'data' => [
                    'id' => '40884',
                    'type' => 'show',
                ],
            ],
        ],
    ],
];

$mockScheduled = array_map(function ($item) {
    $item['attributes']['status'] = 'scheduled';

    return $item;
}, $mockData);

$mockDraft = array_map(function ($item) {
    $item['attributes']['status'] = 'draft';

    return $item;
}, $mockData);

it('returns daily readings', function () use ($mockData, $mockScheduled, $mockDraft) {
    // Arrange
    $this->transistorServiceMock->shouldReceive('getAll')
        ->with('wildatheart-daily')
        ->once()
        ->andReturn($mockData);

    $this->transistorServiceMock->shouldReceive('getAll')
        ->with('wildatheart-daily', 'scheduled')
        ->once()
        ->andReturn($mockScheduled);

    $this->transistorServiceMock->shouldReceive('getAll')
        ->with('wildatheart-daily', 'draft')
        ->once()
        ->andReturn($mockDraft);

    // Act
    $response = getJson(route('content.v1.dailyReading.index'));

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'transistor_episode' => [
                    'attributes' => [
                        'slug',
                        'title',
                        // other attributes...
                    ],
                ],
                'current_episode_slug',
            ],
        ]);

    $responseData = $response->json();

    expect($responseData[0]['current_episode_slug'])->toEqual('what-jesus-weeps-over')
        ->and($responseData[1]['current_episode_slug'])->toEqual('his-child-his-reflection')
        ->and($responseData[2]['current_episode_slug'])->toEqual('what-jesus-weeps-over')
        ->and($responseData[3]['current_episode_slug'])->toEqual('his-child-his-reflection')
        ->and($responseData[4]['current_episode_slug'])->toEqual('what-jesus-weeps-over')
        ->and($responseData[5]['current_episode_slug'])->toEqual('his-child-his-reflection');
});

it('returns limited number of recent daily readings', function () use ($mockData) {
    // Arrange
    $limit = 2;
    $this->transistorServiceMock->shouldReceive('getRecent')
        ->with('wildatheart-daily', $limit)
        ->once()
        ->andReturn($mockData);

    // Act
    $response = getJson(route('content.v1.dailyReading.getRecent', ['limit' => $limit]));

    // Assert
    $response->assertStatus(200)
        ->assertJsonCount($limit)
        ->assertJsonStructure([
            '*' => [
                'transistor_episode' => [
                    'attributes' => [
                        'slug',
                        'title',
                        // other attributes...
                    ],
                ],
                'current_episode_slug',
            ],
        ]);
});

it('uses default limit when none provided for recent readings', function () use ($mockData) {
    // Arrange
    $defaultLimit = 1;
    $this->transistorServiceMock->shouldReceive('getRecent')
        ->with('wildatheart-daily', $defaultLimit)
        ->once()
        ->andReturn([$mockData[0]]);

    // Act
    $response = getJson(route('content.v1.dailyReading.getRecent'));

    // Assert
    $response->assertStatus(200)
        ->assertJsonCount($defaultLimit);
});

it('returns redirects', function () {

    // Act
    $response = getJson(route('content.v1.dailyReading.redirect', ['slug' => 'what-jesus-weeps-over']));

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'previous_slug',
            'current_slug',
        ]);

    $responseData = $response->json();
    expect($responseData['previous_slug'])->toEqual('what-jesus-weeps-over');
    expect($responseData['current_slug'])->toEqual('what-jesus-weeps-over');
});

it('returns redirects using the old slug', function () {
    // Act
    $response = getJson(route('content.v1.dailyReading.redirect', ['slug' => 'his-child-his-reflection']));

    // Assert
    $responseData = $response->json();
    expect($responseData['current_slug'])->toEqual('his-child-his-reflection');
    expect($responseData['previous_slug'])->toEqual('his-child-his-reflection');
});
