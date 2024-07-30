<?php

use App\Actions\Address\CreateAddress;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\EventTypeSeeder;

beforeEach(function () {
    User::factory()->create();
    $this->seed(EmailTemplateSeeder::class);
    $this->seed(EventTypeSeeder::class);
});
test('an address is added to the database', function () {
    $event = Event::factory()->fires()->create();
    $attributes = [
        'given_name' => 'Steven',
        'family_name' => 'Grant',
        'company' => null,
        'street_address' => '18 Greenlees Road',
        'address2' => null,
        'city' => 'Glasgow',
        'state' => null,
        'zip' => 'G72 8JJ',
        'phone' => '079 1234 5678',
        'is_primary' => true,
        'country' => 'UK',
        'event_id' => $event->id,
    ];

    CreateAddress::run($attributes);

    $this->assertDatabaseHas('addresses', $attributes);
});
