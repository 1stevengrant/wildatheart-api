<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('uses the Laravel 13 request forgery middleware', function (): void {
    $middleware = app('router')->getMiddlewareGroups()['non-inertia'];

    expect($middleware)->toContain(PreventRequestForgery::class);
});

it('disables arbitrary cache class unserialization', function (): void {
    expect(config('cache.serializable_classes'))->not->toBeTrue();
});

it('disables avatar caching that stores encoded image objects', function (): void {
    expect(config('laravolt.avatar.cache.enabled'))->toBeFalse();
});

it('stores home page cache values as primitives', function (): void {
    configureSerializedArrayCache();

    $this->get(route('home'))->assertSuccessful();
    $this->get(route('home'))->assertSuccessful();

    expectCacheValueToContainOnlyPrimitives(Cache::get('allEventTypes.v2'));
    expectCacheValueToContainOnlyPrimitives(Cache::get('experienceTypes.v2'));
    expectCacheValueToContainOnlyPrimitives(Cache::get('retreatTypes.v2'));
    expectCacheValueToContainOnlyPrimitives(Cache::get('fireTypes.v2'));
});

it('stores grouped event type cache values as primitives', function (): void {
    configureSerializedArrayCache();

    $this->getJson(route('v1.events.types.grouped'))->assertSuccessful();
    $this->getJson(route('v1.events.types.grouped'))->assertSuccessful();

    expectCacheValueToContainOnlyPrimitives(Cache::get('eventTypesGroupedByType.v2'));
});
