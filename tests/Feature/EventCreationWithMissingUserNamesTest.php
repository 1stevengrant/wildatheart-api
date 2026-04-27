<?php

use App\Models\User;
use App\Models\EventType;
use App\Jobs\PostToZapier;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
    ]);
    $this->eventType = EventType::find(2);
});

it('needs a user with a family and given name to create an event', function () {

    $eventAttributes = [
        'event_type_id' => $this->eventType->id,
        'name' => 'Test Event',
        'city' => 'Test City',
        'zip' => '12345',
        'country' => 'Test Country',
        'latitude' => 12.345678,
        'longitude' => 98.765432,
        'region' => 'Test Region',
        'district' => 'Test District',
        'locality' => 'Test Locality',
        'neighborhood' => 'Test Neighborhood',
        'street_address' => '123 Test St',
        'is_private' => false,
        'state' => 'Test State',
        'start_date' => now(),
        'end_date' => now()->addDays(2),
        'number_of_items_in_material_order' => '5',
    ];

    $this->actingAs($this->user)
        ->post(route('host.eventType.create.store', $this->eventType), $eventAttributes);

    Queue::assertNotPushed(PostToZapier::class);
});

it('allows a user with a family and given name to create an event', function () {
    $this->withoutExceptionHandling();
    $this->actingAs($this->user)
        ->post(route('host.eventType.mailing.store', $this->eventType), [
            'given_name' => 'John',
            'family_name' => 'Doe',
            'street_address' => '123 Main St',
            'address2' => '',
            'company' => '',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => '12345',
            'country' => 'Test Country',
            'phone' => '123-456-7890',
        ]);

    expect($this->user->addresses()->first()->street_address)->toBe('123 Main St');

    $this->actingAs($this->user)
        ->post(route('host.eventType.create.store', $this->eventType), [
            'event_type_id' => $this->eventType->id,
            'name' => 'Test Event',
            'city' => 'Test City',
            'zip' => '12345',
            'country' => 'Test Country',
            'latitude' => 12.345678,
            'longitude' => 98.765432,
            'region' => 'Test Region',
            'district' => 'Test District',
            'locality' => 'Test Locality',
            'neighborhood' => 'Test Neighborhood',
            'street_address' => '123 Test St',
            'is_private' => false,
            'state' => 'Test State',
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'number_of_items_in_material_order' => \App\Models\Event::MATERIAL_ORDER_ITEMS[3]['value'],
        ]);
    Queue::assertPushed(PostToZapier::class);
});
