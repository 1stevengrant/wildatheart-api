<?php

use App\Actions\Address\CreateAddress;
use App\Models\Campfire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('an address is added to the database', function () {
    $campfire = Campfire::factory()->create();
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
        'campfire_id' => $campfire->id,
    ];

    CreateAddress::run($attributes);

    $this->assertDatabaseHas('addresses', $attributes);
});
