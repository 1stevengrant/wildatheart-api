<?php

use App\DataTransferObjects\GoogleSheetDataTransferObject;
use App\Jobs\PostToZapier;
use App\Models\EventType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);

    $this->user = User::factory()->create();

    $this->eventType = EventType::where('name', '=', EventType::WAH_BASIC)->first();

    $this->mailingAddressAttributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'street_address' => '123 Main St',
        'address2' => 'Apt 1',
        'company' => 'Acme Inc',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '62701',
        'country' => 'US',
        'phone' => '555-555-5555',
    ];
});

test('a known user can create a new event', function () {
    $this->withoutExceptionHandling();
    Queue::fake();

    // Create mailing address
    $response = $this->actingAs($this->user)
        ->post(route('host.eventType.mailing.store', $this->eventType), $this->mailingAddressAttributes);

    $response->assertRedirect(route('host.eventType.create', $this->eventType));

    $this->assertDatabaseHas('addresses', [
        'given_name' => $this->mailingAddressAttributes['given_name'],
        'family_name' => $this->mailingAddressAttributes['family_name'],
        'street_address' => $this->mailingAddressAttributes['street_address'],
        'address2' => $this->mailingAddressAttributes['address2'],
        'company' => $this->mailingAddressAttributes['company'],
        'city' => $this->mailingAddressAttributes['city'],
        'state' => $this->mailingAddressAttributes['state'],
        'zip' => $this->mailingAddressAttributes['zip'],
        'country' => $this->mailingAddressAttributes['country'],
        'phone' => $this->mailingAddressAttributes['phone'],
        'is_primary' => true,
    ]);

    $eventData = [
        'name' => 'My Event',
        'city' => $this->mailingAddressAttributes['city'],
        'zip' => $this->mailingAddressAttributes['zip'],
        'country' => $this->mailingAddressAttributes['country'],
        'latitude' => 39.78,
        'longitude' => -89.65,
        'region' => 'Midwest',
        'district' => 'Central',
        'locality' => 'Springfield',
        'neighborhood' => 'Downtown',
        'street_address' => $this->mailingAddressAttributes['street_address'],
        'is_private' => false,
        'state' => $this->mailingAddressAttributes['state'],
        'start_date' => '2022-01-01',
        'end_date' => '2022-01-02',
        'number_of_items_in_material_order' => 1,
    ];

    $response = $this->actingAs($this->user)
        ->post(route('host.eventType.create.store', $this->eventType), $eventData);

    $event = $this->user->events()->where('name', '=', $eventData['name'])->first();
    expect($event)->not->toBeNull()
        ->and($event->event_type_id)->toBe($this->eventType->id)
        ->and($event->number_of_items_in_material_order)->toBe($eventData['number_of_items_in_material_order'])
        ->and($event->name)->toBe($eventData['name'])
        ->and($event->latitude)->toBe($eventData['latitude'])
        ->and($event->longitude)->toBe($eventData['longitude']);

    $response->assertRedirect(route('my.events.active', ['event' => $event->id]));

    Queue::assertPushed(PostToZapier::class, function (PostToZapier $job) use ($event) {
        return $job->dataTransferObject instanceof GoogleSheetDataTransferObject
            && $job->dataTransferObject->webhookUrl === $this->eventType->zapier_webhook_url
            && $job->dataTransferObject->eventId === $event->id
            && $job->dataTransferObject->eventType === $this->eventType->name
            && $job->dataTransferObject->email === $this->user->email
            && $job->dataTransferObject->first_name === $this->user->given_name
            && $job->dataTransferObject->last_name === $this->user->family_name
            && $job->dataTransferObject->address_1 === $this->mailingAddressAttributes['street_address']
            && $job->dataTransferObject->address_2 === $this->mailingAddressAttributes['address2']
            && $job->dataTransferObject->city === $this->mailingAddressAttributes['city']
            && $job->dataTransferObject->state === $this->mailingAddressAttributes['state']
            && $job->dataTransferObject->zip === $this->mailingAddressAttributes['zip']
            && $job->dataTransferObject->quantity === 1;
    });
});
