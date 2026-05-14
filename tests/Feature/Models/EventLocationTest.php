<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns empty string when neither country nor city set', function () {
    $event = Event::factory()->fires()->create([
        'country' => null,
        'city' => null,
    ]);

    expect($event->location)->toBe('');
});

it('returns just the city when country is null', function () {
    $event = Event::factory()->fires()->create([
        'country' => null,
        'city' => 'Phoenix',
    ]);

    expect($event->location)->toBe('Phoenix');
});

it('US with city and region returns city and state abbreviation', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'US',
        'city' => 'Phoenix',
        'region' => 'AZ',
    ]);

    expect($event->location)->toBe('Phoenix, AZ');
});

it('united states (full name) with city and region returns city and state abbreviation', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'United States',
        'city' => 'Phoenix',
        'region' => 'AZ',
    ]);

    expect($event->location)->toBe('Phoenix, AZ');
});

it('US with city but no region falls through to uppercased country', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'US',
        'city' => 'Phoenix',
        'region' => null,
    ]);

    expect($event->location)->toBe('Phoenix, US');
});

it('new zealand with city and region returns city and New Zealand', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'New Zealand',
        'city' => 'Auckland',
        'region' => 'Auckland',
    ]);

    expect($event->location)->toBe('Auckland, New Zealand');
});

it('new zealand with city but no region still returns city and New Zealand', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'New Zealand',
        'city' => 'Auckland',
        'region' => null,
    ]);

    expect($event->location)->toBe('Auckland, New Zealand');
});

it('gb with city and region returns city and UK', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'gb',
        'city' => 'London',
        'region' => 'England',
    ]);

    expect($event->location)->toBe('London, UK');
});

it('gb with city but no region still returns city and UK', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'gb',
        'city' => 'London',
        'region' => null,
    ]);

    expect($event->location)->toBe('London, UK');
});

it('united kingdom (full name) with city returns city and UK', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'United Kingdom',
        'city' => 'London',
        'region' => null,
    ]);

    expect($event->location)->toBe('London, UK');
});

it('other country returns city and uppercased country code', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'AU',
        'city' => 'Sydney',
        'region' => null,
    ]);

    expect($event->location)->toBe('Sydney, AU');
});
