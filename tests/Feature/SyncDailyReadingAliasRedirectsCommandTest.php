<?php

use Illuminate\Support\Str;
use App\Services\RedirectService;
use Illuminate\Support\Facades\Artisan;

function dailyReadingsTempPath(string $suffix): string
{
    return storage_path('framework/testing/dailyreadings-' . $suffix . '.json');
}

it('does not call createBulk when using dry-run', function () {
    $mock = Mockery::mock(RedirectService::class);
    $mock->shouldNotReceive('createBulk');
    $this->app->instance(RedirectService::class, $mock);

    $path = dailyReadingsTempPath('dry-run');
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, json_encode([
        ['alias' => '/daily-reading/x', 'title' => 'Any Title'],
    ]));

    $this->artisan('redirects:sync-daily-reading-aliases', ['--path' => $path, '--dry-run' => true])
        ->assertSuccessful();

    unlink($path);
});

it('chunks createBulk calls according to --chunk', function () {
    $path = dailyReadingsTempPath('chunk');
    file_put_contents($path, json_encode([
        ['alias' => '/daily-reading/a', 'title' => 'Title A'],
        ['alias' => '/daily-reading/b', 'title' => 'Title B'],
        ['alias' => '/daily-reading/c', 'title' => 'Title C'],
    ]));

    $mock = Mockery::mock(RedirectService::class);
    $mock->shouldReceive('createBulk')->once()->withArgs(function ($chunk) {
        return count($chunk) === 2;
    });
    $mock->shouldReceive('createBulk')->once()->withArgs(function ($chunk) {
        return count($chunk) === 1;
    });
    $this->app->instance(RedirectService::class, $mock);

    $this->artisan('redirects:sync-daily-reading-aliases', ['--path' => $path, '--chunk' => 2])
        ->assertSuccessful();

    unlink($path);
});

it('dry-run output includes redirects built from json (skip when alias matches slug)', function () {
    $path = dailyReadingsTempPath('build');
    file_put_contents($path, json_encode([
        ['alias' => '/daily-reading/legacy-slug', 'title' => 'Brand New Title'],
        ['alias' => '/daily-readings/unchanged', 'title' => 'Unchanged'],
    ]));

    Artisan::call('redirects:sync-daily-reading-aliases', ['--path' => $path, '--dry-run' => true]);
    $output = Artisan::output();

    expect($output)->toContain('/daily-reading/legacy-slug')
        ->and($output)->toContain('/daily-readings/' . Str::slug('Brand New Title', '-'))
        ->and($output)->not->toContain('Unchanged');

    unlink($path);
});

it('deduplicates by source path keeping the last row', function () {
    $path = dailyReadingsTempPath('dedupe');
    file_put_contents($path, json_encode([
        ['alias' => 'daily-reading/first-path', 'title' => 'Alpha'],
        ['alias' => '/daily-reading/first-path', 'title' => 'Beta Version'],
    ]));

    Artisan::call('redirects:sync-daily-reading-aliases', ['--path' => $path, '--dry-run' => true]);
    $output = Artisan::output();

    expect($output)->toContain('/daily-readings/' . Str::slug('Beta Version', '-'))
        ->and($output)->not->toContain(Str::slug('Alpha', '-'));

    unlink($path);
});

it('fails when the json file is missing', function () {
    $this->artisan('redirects:sync-daily-reading-aliases', ['--path' => '/nonexistent/dailyreadings.json'])
        ->assertFailed();
});

it('fails when the json is invalid', function () {
    $path = dailyReadingsTempPath('invalid');
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, 'not-json');

    $this->artisan('redirects:sync-daily-reading-aliases', ['--path' => $path])
        ->assertFailed();

    unlink($path);
});
