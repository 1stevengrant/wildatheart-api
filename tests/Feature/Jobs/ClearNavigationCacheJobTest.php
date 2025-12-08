<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use App\Jobs\ClearNavigationCacheJob;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('clears all navigation cache keys', function () {
    Cache::put('navigation.desktop_nav', ['test' => 'desktop'], 60);
    Cache::put('navigation.mobile_nav', ['test' => 'mobile'], 60);
    Cache::put('navigation.all', ['test' => 'all'], 60);

    ClearNavigationCacheJob::dispatchSync('desktop_nav');

    expect(Cache::get('navigation.desktop_nav'))->toBeNull()
        ->and(Cache::get('navigation.mobile_nav'))->toBeNull()
        ->and(Cache::get('navigation.all'))->toBeNull();
});

it('logs activity with global handle and user', function () {
    $user = User::factory()->create();

    Cache::put('navigation.desktop_nav', ['test' => 'desktop'], 60);

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
