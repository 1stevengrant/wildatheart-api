<?php

use App\Jobs\Assets\GenerateColorPalette;
use ColorThief\Exception\NotReadableException;

it('generates a color palette for an asset', function () {
    $asset = Mockery::mock('Statamic\Assets\Asset');
    $asset->shouldReceive('set')
        ->once()
        ->with('color_palette', ['#ff0000', '#00ff00', '#0000ff'])
        ->andReturnSelf();
    $asset->shouldReceive('save')->once();

    $job = Mockery::mock(GenerateColorPalette::class, [$asset])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $job->shouldReceive('getPalette')
        ->once()
        ->andReturn(['#ff0000', '#00ff00', '#0000ff']);

    $job->handle();
});

it('handles blank or transparent images gracefully', function () {
    $asset = Mockery::mock('Statamic\Assets\Asset');
    $asset->shouldReceive('set')
        ->once()
        ->with('color_palette', null)
        ->andReturnSelf();
    $asset->shouldReceive('save')->once();

    $job = Mockery::mock(GenerateColorPalette::class, [$asset])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $job->shouldReceive('getPalette')
        ->once()
        ->andThrow(new RuntimeException('Unable to compute the color palette of a blank or transparent image.'));

    $job->handle();
});

it('handles unreadable images gracefully', function () {
    $asset = Mockery::mock('Statamic\Assets\Asset');
    $asset->shouldReceive('set')
        ->once()
        ->with('color_palette', null)
        ->andReturnSelf();
    $asset->shouldReceive('save')->once();

    $job = Mockery::mock(GenerateColorPalette::class, [$asset])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $job->shouldReceive('getPalette')
        ->once()
        ->andThrow(new NotReadableException('Unable to read image from binary data.'));

    $job->handle();
});

it('rethrows non-transparent image exceptions', function () {
    $asset = Mockery::mock('Statamic\Assets\Asset');

    $job = Mockery::mock(GenerateColorPalette::class, [$asset])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $job->shouldReceive('getPalette')
        ->once()
        ->andThrow(new RuntimeException('Some other error'));

    $job->handle();
})->throws(RuntimeException::class, 'Some other error');
