<?php

use App\Models\Event;
use Laravel\Pennant\Feature;
use Illuminate\Support\Facades\Bus;
use App\Data\V1\EventCollectionData;
use Illuminate\Support\Facades\Mail;
use App\Features\NewEventLandingFeature;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    Feature::purge(NewEventLandingFeature::class);
});

it('returns the legacy event url when the feature flag is inactive', function () {
    Feature::deactivate(NewEventLandingFeature::class);

    $event = Event::factory()->fires()->create();

    expect($event->url)->toBe(route('view.event', ['event' => $event]));
});

it('returns the new event url when the feature flag is active', function () {
    Feature::activate(NewEventLandingFeature::class);

    $event = Event::factory()->fires()->create();

    expect($event->url)->toBe("/events/attend/{$event->id}");
});

it('includes url in EventCollectionData when the feature flag is inactive', function () {
    Feature::deactivate(NewEventLandingFeature::class);

    $event = Event::factory()->fires()->create();
    $data = EventCollectionData::fromEvent($event);

    expect($data->url)->toBe(route('view.event', ['event' => $event]));
});

it('includes url in EventCollectionData when the feature flag is active', function () {
    Feature::activate(NewEventLandingFeature::class);

    $event = Event::factory()->fires()->create();
    $data = EventCollectionData::fromEvent($event);

    expect($data->url)->toBe("/events/attend/{$event->id}");
});

it('defaults to inactive', function () {
    expect(Feature::active(NewEventLandingFeature::class))->toBeFalse();
});
