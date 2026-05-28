<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns the stats', function () {
    $this->getJson(route('v1.stats'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'retreats' => [
                    '*' => [
                        'label',
                        'count',
                    ],
                ],
                'experiences' => [
                    '*' => [
                        'label',
                        'count',
                    ],
                ],
                'fires' => [
                    '*' => [
                        'label',
                        'count',
                    ],
                ],
            ],
        ]);
});

it('caches the stats', function () {
    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->assertTrue(cache()->has('event_stats'));
});

it('caches the stats for an hour', function () {
    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->travel(61)->minutes();

    $this->getJson(route('v1.stats'))
        ->assertOk();

    $this->assertTrue(cache()->has('event_stats'));
});

it('returns the number of upcoming retreats', function () {
    Event::factory()->fires()->create();
    Event::factory()->wahRetreat()->create();
    Event::factory()->captivatingCoreRetreat()->create();
    Event::factory()->becomingAKingRetreat()->create();

    $this->getJson(route('v1.stats'))
        ->assertOk()
        ->assertJsonFragment([
            'label' => 'Current Retreat Listings',
            'count' => 3,
        ]);
});

it('returns the number of fires run in the past year', function () {
    Event::factory()->fires()->approved()->create([
        'start_date' => now()->subMonths(6),
    ]);
    Event::factory()->fires()->approved()->create([
        'start_date' => now()->subMonths(14),
    ]);
    Event::factory()->fires()->approved()->create([
        'start_date' => null,
    ]);
    Event::factory()->fires()->create([
        'start_date' => now()->subMonths(3),
    ]);

    cache()->forget('event_stats');

    $this->getJson(route('v1.stats'))
        ->assertOk()
        ->assertJsonFragment([
            'label' => 'Fires run in the past year',
            'count' => 3,
        ]);
});

it('returns the number of countries with current fires', function () {
    Event::factory()->fires()->approved()->create([
        'country' => 'US',
    ]);
    Event::factory()->fires()->approved()->create([
        'country' => 'CA',
    ]);
    Event::factory()->fires()->approved()->create([
        'country' => 'US',
    ]);
    Event::factory()->fires()->create([
        'country' => 'MX',
    ]);

    cache()->forget('event_stats');

    $this->getJson(route('v1.stats'))
        ->assertOk()
        ->assertJsonFragment([
            'label' => 'Countries with current fires',
            'count' => 2,
        ]);
});
