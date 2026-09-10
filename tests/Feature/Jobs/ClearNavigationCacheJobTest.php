<?php

use App\Models\User;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Jobs\ClearNavigationCacheJob;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Bus::fake()->except([ClearNavigationCacheJob::class, LogActivityJob::class]);
    Mail::fake();
});

it('clears all navigation cache keys', function () {
    Cache::put('navigation.desktop_nav.v2', ['test' => 'desktop'], 60);
    Cache::put('navigation.mobile_nav.v2', ['test' => 'mobile'], 60);
    Cache::put('navigation.all.v2', ['test' => 'all'], 60);

    ClearNavigationCacheJob::dispatchSync('desktop_nav');

    expect(Cache::get('navigation.desktop_nav.v2'))->toBeNull()
        ->and(Cache::get('navigation.mobile_nav.v2'))->toBeNull()
        ->and(Cache::get('navigation.all.v2'))->toBeNull();
});

it('logs activity with global handle and user', function () {
    $user = User::factory()->create();

    Cache::put('navigation.desktop_nav.v2', ['test' => 'desktop'], 60);

    ClearNavigationCacheJob::dispatchSync(
        globalHandle: 'desktop_nav',
        user: $user
    );

    $activity = Activity::query()
        ->where('log_name', 'cache')
        ->where('description', 'Navigation cache cleared')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and((int) $activity->causer_id)->toBe($user->id)
        ->and($activity->statamic_global_handle)->toBe('desktop_nav')
        ->and($activity->properties['trigger'])->toBe('desktop_nav')
        ->and($activity->properties['keys_cleared'])->toBeArray();
});
