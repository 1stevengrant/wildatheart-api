<?php

use App\Models\Event;
use App\Data\V1\EventData;
use Laravel\Pennant\Feature;
use App\Features\NewConnectUrl;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    Feature::purge(NewConnectUrl::class);
});

it('returns the legacy connect url when the feature flag is inactive', function () {
    Feature::deactivate(NewConnectUrl::class);

    $event = Event::factory()->fires()->create();

    expect($event->connect_url)->toBe(config('app.url') . "/connect/{$event->id}");
});

it('returns the new connect url when the feature flag is active', function () {
    Feature::activate(NewConnectUrl::class);

    $event = Event::factory()->fires()->create();

    expect($event->connect_url)->toBe("/events/connect/{$event->id}");
});

it('includes connect_url in EventData', function () {
    Feature::deactivate(NewConnectUrl::class);

    $event = Event::factory()->fires()->create();
    $data = EventData::fromEvent($event);

    expect($data->connect_url)->toBe(config('app.url') . "/connect/{$event->id}");
});

it('includes connect_url in EventData when the feature flag is active', function () {
    Feature::activate(NewConnectUrl::class);

    $event = Event::factory()->fires()->create();
    $data = EventData::fromEvent($event);

    expect($data->connect_url)->toBe("/events/connect/{$event->id}");
});
it('defaults to inactive', function () {
    expect(Feature::active(NewConnectUrl::class))->toBeFalse();
});
