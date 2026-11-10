<?php

use App\Services\Subsplash\SubsplashApiClient;

it('handles null media items in response without throwing', function () {
    $apiClient = Mockery::mock(SubsplashApiClient::class);

    $apiClient->shouldReceive('getPublishedMediaItemsUrl')
        ->once()
        ->andReturn('https://core.subsplash.com/media/v1/media-items?filter=test');

    $apiClient->shouldReceive('fetch')
        ->with('https://core.subsplash.com/media/v1/media-items?filter=test', 30)
        ->once()
        ->andReturn([
            '_embedded' => [],
            '_links' => [],
        ]);

    $apiClient->shouldReceive('getMediaItem')->never();

    $this->app->instance(SubsplashApiClient::class, $apiClient);

    $this->artisan('app:get-latest-subsplash-media')
        ->assertSuccessful();
});
