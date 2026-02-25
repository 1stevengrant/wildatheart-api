<?php

use App\Services\SubsplashService;

it('handles null media items in response without throwing', function () {
    $subsplash = Mockery::mock(SubsplashService::class);

    $subsplash->shouldReceive('subsplashFetch')
        ->once()
        ->andReturn([
            '_embedded' => [],
            '_links' => [],
        ]);

    $this->app->instance(SubsplashService::class, $subsplash);

    $this->artisan('app:get-latest-subsplash-media')
        ->assertSuccessful();
});
