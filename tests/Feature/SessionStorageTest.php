<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Artisan;

test('the composer build does not clear the application cache', function () {
    $composer = json_decode(file_get_contents(base_path('composer.json')), true);

    $statamicInstall = collect($composer['scripts']['post-autoload-dump'])
        ->first(fn (string $script): bool => str_contains($script, 'statamic:install'));

    expect($statamicInstall)->toContain('--no-cache-clear');
});

test('database sessions survive clearing the application cache', function () {
    config(['session.driver' => 'database']);

    $session = app('session')->driver('database');
    $sessionId = Str::random(40);

    $session->setId($sessionId);
    $session->start();
    $session->put('login_web', 1);
    $session->save();

    Cache::put('some-cached-value', 'cached');

    Artisan::call('cache:clear');

    $reloaded = (new SessionManager(app()))->driver('database');
    $reloaded->setId($sessionId);
    $reloaded->start();

    expect(Cache::get('some-cached-value'))->toBeNull()
        ->and(DB::table('sessions')->where('id', $sessionId)->exists())->toBeTrue()
        ->and($reloaded->get('login_web'))->toBe(1);
});
