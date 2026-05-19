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

it('returns empty string when city is not set', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'US',
        'city' => null,
        'region' => 'AZ',
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

it('US territory in region falls back to the region name when no state abbreviation exists', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'us',
        'city' => 'San Juan',
        'region' => 'Puerto Rico',
    ]);

    expect($event->location)->toBe('San Juan, Puerto Rico');
});

it('US with city but no region falls through to full country name', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'US',
        'city' => 'Phoenix',
        'region' => null,
    ]);

    expect($event->location)->toBe('Phoenix, United States');
});

it('nz (2-letter code, lowercase) returns city and New Zealand', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'nz',
        'city' => 'Auckland',
        'region' => null,
    ]);

    expect($event->location)->toBe('Auckland, New Zealand');
});

it('NZ (2-letter code, uppercase) returns city and New Zealand', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'NZ',
        'city' => 'Auckland',
        'region' => null,
    ]);

    expect($event->location)->toBe('Auckland, New Zealand');
});

it('new zealand (full name) returns city and New Zealand', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'New Zealand',
        'city' => 'Auckland',
        'region' => 'Auckland',
    ]);

    expect($event->location)->toBe('Auckland, New Zealand');
});

it('ca (2-letter code, lowercase) returns city and Canada', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'ca',
        'city' => 'Grand Forks',
        'region' => null,
    ]);

    expect($event->location)->toBe('Grand Forks, Canada');
});

it('CA (2-letter code, uppercase) returns city and Canada', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'CA',
        'city' => 'Grand Forks',
        'region' => null,
    ]);

    expect($event->location)->toBe('Grand Forks, Canada');
});

it('canada (full name) returns city and Canada', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'Canada',
        'city' => 'Grand Forks',
        'region' => 'BC',
    ]);

    expect($event->location)->toBe('Grand Forks, Canada');
});

it('br (2-letter code, lowercase) returns city and Brazil', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'br',
        'city' => 'Cambé',
        'region' => 'Paraná',
    ]);

    expect($event->location)->toBe('Cambé, Brazil');
});

it('cl (2-letter code, lowercase) returns city and Chile', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'cl',
        'city' => 'La Reina',
        'region' => 'Santiago Metropolitan Region',
    ]);

    expect($event->location)->toBe('La Reina, Chile');
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

it('two-letter country code resolves to full country name', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'AU',
        'city' => 'Sydney',
        'region' => null,
    ]);

    expect($event->location)->toBe('Sydney, Australia');
});

it('unknown country code falls back to ucfirst', function () {
    $event = Event::factory()->fires()->create([
        'country' => 'XX',
        'city' => 'Nowhere',
        'region' => null,
    ]);

    expect($event->location)->toBe('Nowhere, XX');
});
