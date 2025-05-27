<?php

use App\Models\EventType;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->user = \App\Models\User::factory()->create();
    $this->actingAs($this->user);
});

it('creates a standard fire', function () {
    $this->withoutExceptionHandling();
    $attributes = [
        'name' => 'Manly',
        'street_address' => '123 Main St',
        'state' => 'CA',
        'city' => 'Los Angeles',
        'zip' => '90001',
        'country' => 'US',
        'latitude' => 34.05,
        'longitude' => -118.24,
        'region' => 'Southern California',
        'district' => 'Greater Los Angeles',
        'locality' => 'Downtown',
        'neighborhood' => 'Skid Row',
        'start_date' => now()->addDays(10)->format('Y-m-d'),
        'frequency' => 'Weekly',
    ];

    $this->post(route('host.fire.store'), $attributes);
    $event = \App\Models\Event::query()->name('Manly')->first();
    expect($event->name)->toBe('Manly')
        ->and($event->eventType->name)->toBe(EventType::FIRES)
        ->and($event->street_address)->toBe('123 Main St')
        ->and($event->state)->toBe('CA')
        ->and($event->city)->toBe('Los Angeles')
        ->and($event->zip)->toBe('90001')
        ->and($event->latitude)->toBe(34.05)
        ->and($event->longitude)->toBe(-118.24)
        ->and($event->region)->toBe('Southern California')
        ->and($event->district)->toBe('Greater Los Angeles')
        ->and($event->locality)->toBe('Downtown')
        ->and($event->neighborhood)->toBe('Skid Row')
        ->and($event->user_id)->toBe($this->user->id);

});
